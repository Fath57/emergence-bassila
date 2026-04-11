// Bassila Émergence — Rich blog editor (TipTap 3)
//
// This module is loaded only on /blog/rediger and /blog/{slug}/modifier
// via @vite('resources/js/editor.js') in the page-specific section.
// It attaches a single TipTap editor to the element carrying `data-editor`,
// wires a toolbar, and bridges the content back to the Livewire `content`
// property via a hidden <textarea wire:model>.

import { Editor } from '@tiptap/core';
import StarterKit from '@tiptap/starter-kit';
import Link from '@tiptap/extension-link';
import Image from '@tiptap/extension-image';
import { Table, TableRow, TableHeader, TableCell } from '@tiptap/extension-table';
import { CodeBlockLowlight } from '@tiptap/extension-code-block-lowlight';
import { Youtube } from '@tiptap/extension-youtube';
import { createLowlight, common } from 'lowlight';
import 'highlight.js/styles/github.css';

const lowlight = createLowlight(common);

window.initBassilaEditor = function initBassilaEditor(root) {
    const hidden = root.querySelector('[data-editor-content]');
    const mount  = root.querySelector('[data-editor-mount]');
    const toolbar = root.querySelector('[data-editor-toolbar]');

    if (!mount || !hidden) return;

    const editor = new Editor({
        element: mount,
        extensions: [
            StarterKit.configure({
                heading: { levels: [2, 3, 4] },
                codeBlock: false, // replaced by lowlight version below
            }),
            Link.configure({
                openOnClick: false,
                HTMLAttributes: { rel: 'noopener noreferrer nofollow', target: '_blank' },
            }),
            Image.configure({
                inline: false,
                HTMLAttributes: { class: 'rounded-none my-6' },
            }),
            CodeBlockLowlight.configure({ lowlight }),
            Table.configure({ resizable: false }),
            TableRow,
            TableHeader,
            TableCell,
            Youtube.configure({ inline: false, width: 720, height: 405 }),
        ],
        content: hidden.value || '',
        editorProps: {
            attributes: {
                class: 'prose prose-lg max-w-none focus:outline-none min-h-[420px] px-4 py-6',
            },
        },
        onUpdate({ editor }) {
            const html = editor.getHTML();
            // Write to the hidden textarea then dispatch input so Livewire
            // picks it up through its wire:model debounce.
            hidden.value = html;
            hidden.dispatchEvent(new Event('input', { bubbles: true }));
        },
    });

    // ─── Toolbar actions ───────────────────────────────────────────────
    if (toolbar) {
        toolbar.addEventListener('click', (e) => {
            const btn = e.target.closest('[data-cmd]');
            if (!btn) return;
            e.preventDefault();
            const cmd = btn.dataset.cmd;
            const chain = editor.chain().focus();

            switch (cmd) {
                case 'bold':         chain.toggleBold().run(); break;
                case 'italic':       chain.toggleItalic().run(); break;
                case 'strike':       chain.toggleStrike().run(); break;
                case 'underline':    chain.toggleMark('underline').run(); break;
                case 'h2':           chain.toggleHeading({ level: 2 }).run(); break;
                case 'h3':           chain.toggleHeading({ level: 3 }).run(); break;
                case 'h4':           chain.toggleHeading({ level: 4 }).run(); break;
                case 'bulletList':   chain.toggleBulletList().run(); break;
                case 'orderedList':  chain.toggleOrderedList().run(); break;
                case 'blockquote':   chain.toggleBlockquote().run(); break;
                case 'codeBlock':    chain.toggleCodeBlock().run(); break;
                case 'hr':           chain.setHorizontalRule().run(); break;
                case 'undo':         chain.undo().run(); break;
                case 'redo':         chain.redo().run(); break;
                case 'link': {
                    const previous = editor.getAttributes('link').href;
                    const url = window.prompt('URL du lien', previous || 'https://');
                    if (url === null) break;
                    if (url === '')      chain.unsetLink().run();
                    else                 chain.setLink({ href: url }).run();
                    break;
                }
                case 'youtube': {
                    const url = window.prompt('URL YouTube', 'https://www.youtube.com/watch?v=');
                    if (url) editor.commands.setYoutubeVideo({ src: url });
                    break;
                }
                case 'image': {
                    const input = document.createElement('input');
                    input.type = 'file';
                    input.accept = 'image/jpeg,image/png,image/webp,image/gif';
                    input.addEventListener('change', async () => {
                        const file = input.files?.[0];
                        if (!file) return;
                        const alt = window.prompt(
                            "Texte alternatif (laissez vide uniquement si l'image est purement décorative)",
                            '',
                        );
                        if (alt === null) return; // cancelled
                        const fd = new FormData();
                        fd.append('image', file);
                        const res = await fetch('/blog/upload-image', {
                            method: 'POST',
                            headers: {
                                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
                                'Accept': 'application/json',
                            },
                            body: fd,
                        });
                        if (!res.ok) {
                            window.alert('Échec du téléversement de l\'image.');
                            return;
                        }
                        const { url } = await res.json();
                        editor.chain().focus().setImage({ src: url, alt }).run();
                    });
                    input.click();
                    break;
                }
            }
        });
    }
};

// Auto-init on every editor root present at DOM ready. Livewire re-renders
// preserve elements with wire:ignore, so we don't need to re-init on
// subsequent updates.
document.addEventListener('DOMContentLoaded', () => {
    document.querySelectorAll('[data-editor]').forEach((root) => window.initBassilaEditor(root));
});

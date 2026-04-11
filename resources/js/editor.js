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

/**
 * Open the image insertion modal and walk the user through:
 *   1. preview the picked file
 *   2. enter alt text
 *   3. upload to /blog/upload-image
 *   4. insert <img> into TipTap
 *
 * Replaces window.prompt() (alt) and window.alert() (upload failure)
 * with a fully-styled Blade modal living under [data-editor-modal].
 *
 * Returns a Promise that resolves with the public URL on success or
 * rejects on cancel / error. The caller is expected to await it from
 * within a toolbar click handler.
 */
function openImageModal(file, editor) {
    const modal       = document.querySelector('[data-editor-modal]');
    if (!modal) {
        // Defensive fallback — if the modal markup is missing for any
        // reason, degrade to the native prompt rather than crashing.
        return Promise.resolve({ nativeFallback: true });
    }

    const panel       = modal.querySelector('[data-editor-modal-panel]');
    const previewWrap = modal.querySelector('[data-editor-modal-preview]');
    const previewImg  = modal.querySelector('[data-editor-modal-preview-img]');
    const input       = modal.querySelector('[data-editor-modal-input]');
    const spinner     = modal.querySelector('[data-editor-modal-spinner]');
    const errorBox    = modal.querySelector('[data-editor-modal-error]');
    const cancelBtn   = modal.querySelector('[data-editor-modal-cancel]');
    const confirmBtn  = modal.querySelector('[data-editor-modal-confirm]');

    // Reset state
    input.value = '';
    errorBox.classList.add('hidden');
    errorBox.textContent = '';
    spinner.classList.add('hidden');
    confirmBtn.disabled = false;
    confirmBtn.textContent = "Insérer l'image";

    // Preview the local file before upload
    const previewUrl = URL.createObjectURL(file);
    previewImg.src   = previewUrl;
    previewWrap.classList.remove('hidden');

    // Show modal
    modal.classList.remove('hidden');
    modal.classList.add('flex');
    // Focus the input next tick so the transition completes
    setTimeout(() => input.focus(), 30);

    return new Promise((resolve) => {
        const cleanup = () => {
            URL.revokeObjectURL(previewUrl);
            modal.classList.add('hidden');
            modal.classList.remove('flex');
            cancelBtn.removeEventListener('click', onCancel);
            confirmBtn.removeEventListener('click', onConfirm);
            document.removeEventListener('keydown', onKey);
            modal.removeEventListener('click', onBackdrop);
        };

        const onCancel = () => {
            cleanup();
            resolve({ cancelled: true });
        };

        const onBackdrop = (e) => {
            // Clicking outside the panel (on the backdrop) cancels
            if (e.target === modal) onCancel();
        };

        const onKey = (e) => {
            if (e.key === 'Escape') { e.preventDefault(); onCancel(); }
            else if (e.key === 'Enter' && document.activeElement === input) {
                e.preventDefault();
                onConfirm();
            }
        };

        const showError = (message) => {
            errorBox.textContent = message;
            errorBox.classList.remove('hidden');
            spinner.classList.add('hidden');
            confirmBtn.disabled = false;
            confirmBtn.textContent = "Réessayer";
        };

        const onConfirm = async () => {
            const alt = input.value.trim();

            // Start upload
            confirmBtn.disabled = true;
            confirmBtn.textContent = "Téléversement…";
            spinner.classList.remove('hidden');
            errorBox.classList.add('hidden');

            const fd = new FormData();
            fd.append('image', file);

            try {
                const csrfMeta = document.querySelector('meta[name="csrf-token"]');
                const res = await fetch('/blog/upload-image', {
                    method: 'POST',
                    headers: {
                        'X-CSRF-TOKEN': csrfMeta ? csrfMeta.content : '',
                        'Accept': 'application/json',
                    },
                    body: fd,
                });

                if (!res.ok) {
                    let msg = "Le téléversement a échoué.";
                    try {
                        const err = await res.json();
                        if (err?.errors?.image?.[0]) msg = err.errors.image[0];
                        else if (err?.message)       msg = err.message;
                    } catch (_) { /* ignore JSON parse issues */ }
                    showError(msg);
                    return;
                }

                const { url } = await res.json();
                editor.chain().focus().setImage({ src: url, alt }).run();
                cleanup();
                resolve({ url });
            } catch (e) {
                showError("Le téléversement a échoué — vérifiez votre connexion.");
            }
        };

        cancelBtn.addEventListener('click', onCancel);
        confirmBtn.addEventListener('click', onConfirm);
        document.addEventListener('keydown', onKey);
        modal.addEventListener('click', onBackdrop);
    });
}

window.initBassilaEditor = function initBassilaEditor(root) {
    // Idempotent: skip roots that already have an editor attached.
    if (root.dataset.editorInitialized === '1') return;

    const hidden = root.querySelector('[data-editor-content]');
    const mount  = root.querySelector('[data-editor-mount]');
    const toolbar = root.querySelector('[data-editor-toolbar]');

    if (!mount || !hidden) return;

    root.dataset.editorInitialized = '1';

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
                    const fileInput = document.createElement('input');
                    fileInput.type = 'file';
                    fileInput.accept = 'image/jpeg,image/png,image/webp,image/gif';
                    fileInput.addEventListener('change', () => {
                        const file = fileInput.files?.[0];
                        if (!file) return;
                        openImageModal(file, editor);
                    });
                    fileInput.click();
                    break;
                }
            }
        });
    }
};

// Auto-init on every editor root. Runs on DOMContentLoaded for first paint
// and on livewire:navigated so the editor also initializes after a wire:navigate
// page transition. init is idempotent via data-editor-initialized.
function initAllEditors() {
    document.querySelectorAll('[data-editor]').forEach((root) => window.initBassilaEditor(root));
}
document.addEventListener('DOMContentLoaded', initAllEditors);
document.addEventListener('livewire:navigated', initAllEditors);

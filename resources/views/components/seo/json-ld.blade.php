@foreach ($blocks as $block)
<script type="application/ld+json">{!! $encode($block) !!}</script>
@endforeach

<picture>
    <source srcset="{{ $srcSet }}"
            type="image/webp"
            sizes="{{ $sizes }}">
    <img src="{{ asset($src) }}" 
         alt="{{ $alt }}" 
         width="{{ $width }}" 
         height="{{ $height }}" 
         @if($lazy) loading="lazy" @endif 
         class="{{ $class }}">
</picture>

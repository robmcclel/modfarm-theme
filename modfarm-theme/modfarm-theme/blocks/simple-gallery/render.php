<?php
if (!defined('ABSPATH')) exit;

function modfarm_render_simple_gallery_block($attributes, $content = '', $block = null) {
    static $did_lightbox = false;

    $images = is_array($attributes['images'] ?? null) ? $attributes['images'] : [];
    if (!$images) return '';

    $columns      = isset($attributes['columns']) ? max(1, min(8, (int)$attributes['columns'])) : 3;
    $size         = isset($attributes['size']) ? sanitize_key($attributes['size']) : 'medium';
    $linkMode     = isset($attributes['linkMode']) ? sanitize_key($attributes['linkMode']) : 'lightbox';
    $responsive   = array_key_exists('responsive', $attributes) ? (bool)$attributes['responsive'] : true;
    $visibleCount = max(0, (int)($attributes['visibleCount'] ?? 0)); // 0 = show all

    if ($linkMode === 'lightbox' && !$did_lightbox) {
        $did_lightbox = true;
        $js = '(function(){
  function $all(s,root){return Array.prototype.slice.call((root||document).querySelectorAll(s));}
  var ob, stage, img, btnX, btnPrev, btnNext, countEl, group=[], idx=-1;
  function build(){
    ob = document.createElement("div");
    ob.className="mfsg-ob";
    ob.innerHTML = \'<button class="mfsg-x" aria-label="Close">✕</button><button class="mfsg-prev" aria-label="Previous">‹</button><div class="mfsg-stage"><img alt=""/></div><button class="mfsg-next" aria-label="Next">›</button><div class="mfsg-count"></div>\';
    document.body.appendChild(ob);
    stage = ob.querySelector(".mfsg-stage"); img = stage.querySelector("img");
    btnX = ob.querySelector(".mfsg-x"); btnPrev = ob.querySelector(".mfsg-prev"); btnNext = ob.querySelector(".mfsg-next"); countEl = ob.querySelector(".mfsg-count");
    btnX.addEventListener("click", close);
    ob.addEventListener("click", function(e){ if(e.target===ob) close(); });
    btnPrev.addEventListener("click", prev); btnNext.addEventListener("click", next);
    document.addEventListener("keydown", function(e){ if(!ob.classList.contains("is-open")) return; if(e.key==="Escape") close(); if(e.key==="ArrowLeft") prev(); if(e.key==="ArrowRight") next(); });
  }
  function open(a){
    if(!ob) build();
    var gid = a.getAttribute("data-mfsg");
    group = $all(\'a.mfsg__link[data-mfsg="\'+gid+\'"]\');
    idx = group.indexOf(a); show(idx);
    ob.classList.add("is-open");
  }
  function close(){ ob.classList.remove("is-open"); group=[]; idx=-1; }
  function show(i){ if(i<0||i>=group.length) return; idx=i; var href=group[idx].getAttribute("href"); img.setAttribute("src", href); countEl.textContent=(idx+1)+" / "+group.length; }
  function prev(){ if(group.length) show((idx-1+group.length)%group.length); }
  function next(){ if(group.length) show((idx+1)%group.length); }
  document.addEventListener("click", function(e){
    var a = e.target.closest && e.target.closest("a.mfsg__link");
    if(!a) return;
    var wrap = a.closest(".mfsg");
    if(!wrap || wrap.getAttribute("data-linkmode")!=="lightbox") return;
    e.preventDefault(); open(a);
  });
})();';
        wp_register_script('modfarm-simple-gallery-lightbox', false, [], '1.0.0', true);
        wp_add_inline_script('modfarm-simple-gallery-lightbox', $js);
        wp_enqueue_script('modfarm-simple-gallery-lightbox');
    }

    $gid   = 'mfsg-' . wp_generate_uuid4();
    $cls   = 'mfsg' . ($responsive ? ' is-responsive' : '');
    $out   = '<div class="'.esc_attr($cls).'" style="--mfsg-cols:'.intval($columns).';" data-linkmode="'.esc_attr($linkMode).'" data-size="'.esc_attr($size).'">';

    $total = count($images);
    $visible = ($visibleCount > 0) ? min($visibleCount, $total) : $total;

    // Render VISIBLE thumbnails (first N)
    for ($i = 0; $i < $visible; $i++) {
        $img = $images[$i];
        if (empty($img['id'])) continue;
        $id = (int)$img['id'];
        $thumb = wp_get_attachment_image($id, $size, false, ['class'=>'mfsg__img','loading'=>'lazy']);
        if (!$thumb) continue;
        $full = wp_get_attachment_image_url($id, 'full');
        if (!$full) continue;

        if ($linkMode === 'lightbox') {
            $out .= '<figure class="mfsg__item"><a href="'.esc_url($full).'" class="mfsg__link" data-mfsg="'.esc_attr($gid).'">'.$thumb.'</a></figure>';
        } elseif ($linkMode === 'file') {
            $out .= '<figure class="mfsg__item"><a href="'.esc_url($full).'" class="mfsg__link" target="_blank" rel="noopener">'.$thumb.'</a></figure>';
        } else {
            $out .= '<figure class="mfsg__item">'.$thumb.'</figure>';
        }
    }

    // Render HIDDEN lightbox-only links for the rest (so lightbox has the full set)
    if ($linkMode === 'lightbox' && $total > $visible) {
        for ($i = $visible; $i < $total; $i++) {
            $img = $images[$i];
            if (empty($img['id'])) continue;
            $id = (int)$img['id'];
            $full = wp_get_attachment_image_url($id, 'full');
            if (!$full) continue;
            // Hidden anchor: not focusable, not visible, but part of the lightbox group
            $out .= '<a href="'.esc_url($full).'" class="mfsg__link mfsg__link--hidden" data-mfsg="'.esc_attr($gid).'" tabindex="-1" aria-hidden="true"></a>';
        }
    }

    $out .= '</div>';
    return $out;
}
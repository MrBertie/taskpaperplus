<ul id="tags">
    <?php
    if ( ! empty($this->tags)) {
        foreach($this->tags as $tag => $freq) {
            echo '<li><span class="tag" title="' . \tpp\lang('tag_click_tip') . '">' . \tpp\no_underscores($tag) . '</span><span class="freq">' .  $freq . '</span></li>';
        }
    } else {
        echo '<span class="just-info">' . \tpp\lang('no_tags') . '</span>';
    }
    ?>
</ul>
<div id="wrapper">
    <div class="slider-wrapper theme-default">
        <div id="slider" class="nivoSlider">
            <?php foreach ($slider_items as $item): ?>
                <a href="<?php echo $item['link'] ?>"><img src="<?php echo $item['image_src'] ?>" data-thumb="<?php echo $item['image_src'] ?>" alt="" title="<?php echo $item['caption'] ?>" /></a>
            <?php endforeach ?>
        </div>
    </div>
</div>

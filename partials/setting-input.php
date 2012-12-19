<?php /* @var $instance DvsNivoSlider */ ?>
<?php 
$options = $instance->getOptions();
$id = $instance->settings_group_name."_".$setting_name;
$name = $instance->settings_group_name."[".$setting_name."]";
$value = $options[$setting_name];
?>
<input id='<?php echo $id ?>' name='<?php echo $name ?>]' size='40' type='text' value='<?php echo $value ?>' />

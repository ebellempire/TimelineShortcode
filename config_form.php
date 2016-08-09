<?php $view = get_view(); ?>

<h2><?php echo __('Settings'); ?></h2>

<fieldset id="feed">

<div class="field">
    <div class="two columns alpha">
        <label for="ts_date_field"><?php echo __('Date Field'); ?></label>
    </div>
    <div class="inputs five columns omega">
        <p class="explanation"><?php echo __('Choose a field to use for sorting results by date. Only valid formatted dates will be used.'); ?></p>
        <?php
        echo $view->formSelect('ts_date_field', get_option('ts_date_field'), array(), TimelineShortcodePlugin::getElementFields()
        );
        ?>
    </div>
</div>
	

</fieldset>

<h2><?php echo __('Usage');?></h2>
<p>Use the shortcode <code>[timeline_list]</code> to add the list to a page. Required arguments are <code>element</code> and <code>value</code>. Optionally, include <code>featured='true'</code> to limit results to only featured items.</p> 
<p>(Note that if you would like to show <em>all</em> results – not just featured items – simply omit the featured <code>argument</code>; setting it to <code>false</code> will show only items that are <em>not</em> featured, which may or may not be the intended result).</p>
<h3>Example</h3>
<p><code>[timeline_list element='Subject' value='some subject' featured='true']</code></p>
<h3>Requirements</h3>
<p><strong>Supported date formats</strong><sup>*</sup>:
	<ul>
	<li>mm/dd/yyy (e.g. 12/31/1999)</li>
   	<li>yyyy (e.g. 1999)</li>
   	<li>circa yyyy (e.g. circa 1999)</li>
   	<li>12311977 (e.g. 12311999)</li>
   	<li>Month yyyy (e.g. December 1999)</li>
	<li>Any two of the above separated by a single hyphen or endash<sup>**</sup></li>
	<li>dd-mm-yyy (e.g. 31-12-1999)</li>
	</ul>
<em><sup>*</sup>Dates before common era are not supported. Years without a specific date will default to January 1st. Month and year combinations without a specific date will default to the first of the month.</em>
<br><em><sup>**</sup>Date spans will be displayed using only the first date.</em>
</p>
<p><strong>Server requirements</strong>: Different server platforms and PHP versions may handle certain dates differently. See <a href="https://secure.php.net/manual/en/function.strtotime.php">documentation for PHP strtotime</a>. If you notice all your properly formatted dates being listed as 1970 (or today), your server probably does not meet the requirements. </p>
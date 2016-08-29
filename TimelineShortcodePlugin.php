<?php
class TimelineShortcodePlugin extends Omeka_Plugin_AbstractPlugin
{

	protected $_hooks = array(
		'install',
		'uninstall',
		'config_form',
		'config',
		'initialize',
	);


	protected $_options = array(
		'ts_date_field'=>'Dublin Core,Date',
	);


	/*
    ** Plugin options
    */

	public function hookConfigForm()
	{
		require dirname(__FILE__) . '/config_form.php';
	}

	public function hookConfig()
	{
		set_option('ts_date_field', $_POST['ts_date_field']);
	}


	/**
	 * Install the plugin.
	 */
	public function hookInstall()
	{
		$this->_installOptions();

	}

	/**
	 * Uninstall the plugin.
	 */
	public function hookUninstall()
	{
		$this->_uninstallOptions();

	}

	/**
	 * Add Shortcode.
	 */

    public function hookInitialize()
    {
        add_shortcode('timeline_list', array($this, 'ts_timelineList'));
    }
    
    public function ts_timelineList($args, $view)
    {
        // converts shortcode for display
        return '<div id="ts-widget-container">'.ts_display_timeline_list($args).'</div>';
    }	
    
	public static function getElementFields(){
		$elements=array();
		foreach (get_record('ElementSet',array('name'=>"Dublin Core"))->getElements() as $element){
			$elements['Dublin Core,'.$element->name]='Dublin Core: '.$element->name;
		}	
		foreach (get_record('ElementSet',array('name'=>"Item Type Metadata"))->getElements() as $element){
			$elements['Item Type Metadata,'.$element->name]='Item Type Metadata: '.$element->name;
		}			
		return $elements;	
	}	    
	
}	



function ts_display_timeline_list($args){
	
	// plugin settings
	$pluginSettings = explode(',',get_option('ts_date_field'));
	$dateSet= $pluginSettings[0];
	$dateElement=$pluginSettings[1];	
	
	// shortcode arguments
	$element = isset($args['element']) ? $args['element'] : false;
	$value = isset($args['value']) ? $args['value'] : false;
	$featured = isset($args['featured']) ? $args['featured'] : null; // value doesn't matter

	if($element && $value){		
		// Do something...
		$query=array('featured'=>$featured,'advanced' => array ( 
				array(
			       'element_id' => ts_get_element_id($element), 
			       'type' => 'is exactly', 
			       'terms' => $value,
			       )
			    ));

		$items=get_records('Item',$query,100);
		if(count($items)>0){
			
			$html='<style type="text/css">
				.timeline-year{
					font-size: 200%;
					font-weight: bold;
					margin: 1em auto;
					border-bottom: 1px solid #ccc;
					padding-bottom: .25em;
				}
				.timeline-section-date{
					font-size: 100%;
					text-transform: uppercase;
					font-weight: normal;
					opacity: .8;
				}
				.timeline-section ul{
					padding-left: 0;
					margin-top: 0;
					list-style-type:none;
				}
				.timeline-section ul li{
					background: #f2f2f2;
					margin: .5em 0;
					padding: 1em;
				}
				.timeline-section ul li img{
					height: 2em;
					margin: 0 1em 0 0;
					vertical-align: bottom;
				}
			</style>';			
			
			$itemsByDate=array();
			foreach($items as $item){
				
				$date=metadata($item,array($dateSet, $dateElement));
				
				if( isset($date) ){
					$itemData=array();
					$itemData['id']=$item->id;
					$itemData['title']=metadata($item,array('Dublin Core','Title'));
					$itemData['description']=metadata($item,array('Dublin Core','Description'));
					$itemData['date_text']=$date;
					$itemData['credit']= metadata($item,array('Dublin Core','Creator')) ? metadata($item,array('Dublin Core','Creator')) : (metadata($item,array('Dublin Core','Contributor')) ? metadata($item,array('Dublin Core','Contributor')) : 'Unknown');
					$itemData['thumbnail'] = metadata($item,'has_files') ? record_image($item,'square_thumbnail') : null;
					
					// push content to array with sortable date keys
					$itemsByDate[ts_date_format('Ymd',$date)][] = $itemData;					
				}
				
			}
			// sort items array by date key
			ksort($itemsByDate); 
			
			
			$yearsAlreadyDisplayed=array();
			foreach($itemsByDate as $dateKey=>$items){
				if(!array_search(ts_date_format('Y',$dateKey), $yearsAlreadyDisplayed)){
					$html.= '<div class="timeline-year year-id-'.ts_date_format('Y',$dateKey).'">'.ts_date_format('Y',$dateKey).'</div>';
					$yearsAlreadyDisplayed[]=ts_date_format('Y',$dateKey);
				}
				
				$html.= '<div id="date-'.$dateKey.'" class="timeline-section">';
				$i=1;
				foreach($items as $item){
					if($i==1){
						$html.='<div class="timeline-section-date">'.ts_date_format('d F',$dateKey).'</div><ul>';
					}
					$html.= '<li><a href="/items/show/'.$item['id'].'">'.$item['thumbnail'].$item['title'].'</a></li>';
					
					$i++;				
				}
				$html.= '</ul></div>';
			}
			return $html;

			
		}else{
			echo '...';
		};
		
		
	}else{
		echo '<strong>Timeline Shortcode Error:</strong> Missing required arguments. Required arguments are element and value.<br>Example: [timeline_list element=\'Subject\' value=\'the subject to match\']<br>Debug: '.json_encode($args);
	}

}


function ts_get_element_id($elementName){
	$e = get_record('Element',array('name'=>$elementName));
	return $e->id;
}

/*
**
Do some basic date parsing
Supported date formats:
	mmddyyyy (12311999)
	mm/dd/yyy (12/31/1999)
   	yyyy (1999)
   	circa yyyy (circa 1999)
	any two of the above separated by a single hyphen or endash (circa 1999 - 2000, 12/31/1999 - 01/01/2000, etc)
	dd-mm-yyy (31-12-1999)
**
*/
function ts_date_format($format,$date){
	// ABT: always be trimming
	$date=trim($date);
	
	// First check to see if we're working with one date or a date span
	// ...for spans, we'll only use the first date
	$checkDate=explode('–',$date); // endash
	if(count($checkDate)==2){
		$date=$checkDate[0]; 
		$date=trim($date);
	}
	$checkDate2=explode('-',$date); // hyphen
	if(count($checkDate2)==2){
		$date=$checkDate2[0]; 
		$date=trim($date);
	}	
	// Remove circa
	$date = str_replace('circa','',$date); 
	$date=trim($date);
	
	// Add january 1st to standalone 4-digit years (otherwise PHP will automatically add today's month and day)
	if( is_numeric($date) && strlen($date) == 4 ){
		$date = '01/01/'.$date; 
	}
	
	// Let PHP do the rest of the work...
	$date = date($format,strtotime($date));
	
	return $date;
}

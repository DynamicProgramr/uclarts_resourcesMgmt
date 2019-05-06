<?php
	/**
	* Plugin Name: Resources Entries Management
	* Plugin URI: http://gr8-code.com
	* Description: This plugin allows custom management of the resourcess pages - specifically, the user entries through gravity forms.
	* Version: 0.0.2
	* Author: Russell Thompson
	* Author URI: http://gr8-code.com
	* License: GPL2
	*/
	
	include "includes/rt-resources-management-functions.php";
        
    // include datepicker scripts.
    function resources_management_script_load()
    {
        wp_enqueue_script("jquery-ui-datepicker");
        wp_register_style("jquery-ui", "https://ajax.googleapis.com/ajax/libs/jqueryui/1.9.0/themes/base/jquery-ui.css");
        wp_enqueue_style("jquery-ui");

        // add the custom js file for this plugin
        wp_register_script("rt-resources-custom-script", plugins_url("/js/rt-resources-management.js", __FILE__), array("jquery", "jquery-ui-datepicker"), "09-2018");
        wp_enqueue_script("rt-resources-custom-script");
    } // end resources_management_script_load function

    add_action("init", "resources_management_script_load");

	function register_manage_resources_page()
	{
		register_post_type("resources_pages", array(	"labels" => array(	"name" => "Resources Management",
															"singular_name" => "Manage Resource",
															"add_new" => "Add New",
															"add_new_item" => "Manage Resources",
															"edit" => "Edit",
															"edit_item" => "Edit Resources Entry",
															"new_item" => "New Resources Entry",
															"view" => "View",
															"view_item" => "View Resources Entry",
															"search_items" => "Search Resources Entries",
															"not_found" => "No Resources Entry Found",
															"not_found_in_trash" => "No Entries Found in Trash",
															"parent" => "Parent Resources Page"),
											"public" => true,
											"menu_position" => 5, // puts it below posts, to position below pages use 20
                                            "supports" => array(	"title",
                                                                    "author",
                                                                    "comments",
                                                                    "thumbnail",
                                                                ),
											"taxonomies" => array(""),
											"has_archive" => true)
						);
	} // end create_resources_page function
	
	add_action("init", "register_manage_resources_page");
	
	function resources_pages_admin()
	{
		add_meta_box("resources_page_meta_box", "Resources Details", "display_resources_page_meta_box", "resources_pages", "normal", "high");
	} // end resources_pages_admin function
	
	add_action("admin_init", "resources_pages_admin");
	
	function display_resources_page_meta_box($resources_page)
	{
		global $wpdb, $lead_id, $form_id, $post_id, $values, $multi;
		$printThis = ""; // this is the stuff that will get displayed below
		$catArgs = array(	"orderby" 	=> "term_id",
							"order"		=> "ASC",
						);
		$categories = get_categories($catArgs); // will need the category id's and names for html elements
		$multi = array(); // this will hold all the possible choices for the multi-elements (things like radio buttons, checkboxes, and selects)
		$theChoices = array(); // holds all possible choices for multi-option fields in the form (like radio, checkbox and select elements)
		
		// get the lead id using the post's id
		$getStatement = "SELECT * FROM wp_rg_lead WHERE post_id = ".$resources_page->ID;
		$getLeadInfo = $wpdb->get_results($getStatement);
		
		if ($getLeadInfo != FALSE)
		{
			foreach ($getLeadInfo AS $theLeadInfo)
			{
				$lead_id = $theLeadInfo->id; // aka the entry number
				$form_id = $theLeadInfo->form_id; // form used to create the entry
				$post_id = $theLeadInfo->post_id;
				$lead = RGFormsModel::get_lead($lead_id); 
				$form = GFFormsModel::get_form_meta($lead['form_id']);
				$tabCount = 1; // this is for keyboard access and is used for the tabindex attribute
				
				// also want to get form names (titles) for display
				$allForms = RGFormsModel::get_forms(null, "title");
				$formTitlesArray = array();
				foreach($allForms AS $theForm)
				{
					$formTitlesArray[$theForm->id] = $theForm->title;
				}
		?>
			<div class="wrap">
				<h4>Post ID: <?php print $post_id; ?>&nbsp;&nbsp;&nbsp;Entry ID: <?php print $lead_id; ?>&nbsp;&nbsp;&nbsp;Form ID: <?php print $form_id; ?> (Name: <?php print $formTitlesArray[$form_id]; ?>)</h4>
				<hr />
				<div class="gform_body">
					<ul id="gfrom_fields_<?php print $form_id; ?>" class="gform_fields top_label description_below">
		<?php
				$values = array();
				
				foreach( $form['fields'] AS $field )
				{
				
					$values[$field['id']] = array	(	'id'		=> $field['id'],
												'type'	=> $field['type'],
												'size'	=> $field['size'],
												'label'	=> $field['label'],
												'value'	=> $lead[$field['id']],
											);
					
					switch ($field['type'])
					{
						case "text":
						case "phone":
						case "email":
						case "website":
						case "time":
						case "post_title":
							switch($field['type'])
							{
								case "phone":
									$thePlaceholder = "###-###-####";
								break;
								
								case "email":
									$thePlaceholder = "you@domain.com";
								break;
								
								case "website":
									$thePlaceholder = "http://";
								break;
								
								default:
									$thePlaceholder = "";
								break;
							} // end inner switch for input type=text
					
							?>
								<li id="field_<?php print $form_id; ?>_<?php print $field['id']; ?>" class="gfield">
									<label class="gfield_label" for="input_<?php print $form_id; ?>_<?php print $field['id']; ?>"><?php print $field['label']; ?></label>
									<div class="ginput_container">
										<input id="input_<?php print $form_id; ?>_<?php print $field['id']; ?>" name="input_<?php print $field['id']; ?>" class="<?php print $field['size']; ?>" type="text" tabindex="<?php print $tabCount; ?>" value="<?php print $lead[$field['id']]; ?>" placeholder="<?php print $thePlaceholder; ?>" />
									</div>
								</li>
							<?php
							$tabCount++;
						break;
						
						case "textarea":
						case "post_excerpt":
							$editorContent = $lead[$field['id']];
							$editorId = "input_".$form_id."_".$field['id'];
							$editorSettings = array	(	"media_buttons" 	=> FALSE,
													"textarea_name"	=> "input_".$field['id'],
													"textarea_height"	=> 25,
													"tabindex"		=> $tabCount,
													"editor_class"		=> "wp-editor-area",
												);
							
							?>
								<li id="field_<?php print $form_id; ?>_<?php print $field['id']; ?>" class="gfield">
									<label class="gfield_label" for="input_<?php print $form_id; ?>_<?php print $field['id']; ?>"><?php print $field['label']; ?></label>
									<div class="ginput_container">
										<?php print wp_editor(stripslashes(wpautop($editorContent)), $editorId, $editorSettings); ?>
									</div>
								</li>
							<?php
							$tabCount++;
						break;
						
						case "radio":
						case "select":
						case "post_category":
						case "checkbox":
						case "multiselect":
							?>
								<li id="field_<?php print $form_id; ?>_<?php print $field['id']; ?>" class="gfield">
									<label class="gfield_label"><?php print $field['label']; ?></label>
										<div class="ginput_container">
											<ul id="input_<?php print $form_id; ?>_<?php print $field['id']; ?>" class="gfield_<?php print $field['type']; ?>">
							<?php
							// extra markup for select elements
							if($field['type'] == "select")
							{
								?>
									<select id="" name="" class="">
								<?php
							}
							elseif($field['type'] == "multiselect")
							{
								?>
									<select id="" name="" class="" multiple>
								<?php
							}
							
							// I want to start the $theChoices array fresh each time.
							unset($theChoices);
							// first get all the options for this field
							$choices = $field['choices'];
							$loopCount = 1;
							foreach($choices AS $choice)
							{
								// I'm keeping $multi in here right now for testing and logging
								$multi[$field['id'].$loopCount] = array	(	'parent'		=> $field['id'],
																	'thisId'		=> $field['id'].".".$loopCount,
																	'text'		=> $choice['text'],
																	'value'		=> $choice['value'],
																	'selected'	=> $choice['isSelected'],
																);
								
								// this is the array that is being used for displaying the cat choices. this array is cleared each time the loop starts again.
								$theChoices[$loopCount] = array(	'parent'		=> $field['id'],
															'thisId'		=> $field['id'].".".$loopCount,
															'text'		=> $choice['text'],
															'value'		=> $choice['value'],
															'selected'	=> $choice['isSelected'],
														);
								
								$loopCount++;
							}
							
								// second get all the selections (checked) from the db
								$getSelectedStatement = "SELECT * FROM wp_rg_lead_detail WHERE lead_id = ".$lead_id." AND field_number > ".$field['id']." AND field_number < ".($field['id'] + 1);
								$getSelected = $wpdb->get_results($getSelectedStatement);
								
								if($getSelected != FALSE)
								{
									unset($theSelectedArray); // make sure array is clean and ready for new input
									$theSelectedArray = array(); // holds the options from $theChoices that the user actually chose (checked items)
									$innerLoopCount = 1;
									foreach($getSelected AS $theSelected)
									{
										$colonAt = strpos($theSelected->value, ":");
										if ($colonAt != FALSE)
										{
											$theSelectedValue = substr($theSelected->value, 0, strlen($theSelected->value) - (strlen($theSelected->value) - $colonAt));
										}
										else
										{
											$theSelectedValue = $theSelected->value;
										}
										
										array_push($theSelectedArray, $theSelectedValue);
										$innerLoopCount++;
									}
									
									$theSelectedArray = array_map(trim, $theSelectedArray);
									
									$hidden_forTest_id = "theSelectedArray_count_".$lead_id."-".$field['id'];
									$hidden_forTest_value = is_array($theSelectedArray) ? "is array and count is ".count($theSelectedArray) : count($theSelectedArray);
									
									?>
										<input type="hidden" id="<?php print $hidden_forTest_id; ?>" value="<?php print $hidden_forTest_value; ?>" class="rt-test-01a" />
									<?php
								} // end $getSelected if that checks to see if any categories were returned from the db as selected in the form
								else
								{
									// for testing -> echo "field id: ".$field['id']."<br />";
									// for the resources type, there is only one option, so the number associated with it may not be in xx.x form
									// example, individual practitioner = 24 not 24.1, so the select statement has to be changed
									$getSelectedStatement2 = "SELECT * FROM wp_rg_lead_detail WHERE lead_id = ".$lead_id." AND field_number = ".$field['id'];
									$getSelected2 = $wpdb->get_results($getSelectedStatement2);
									
									if($getSelected2 != FALSE)
									{
										unset($theSelectedArray); // make sure array is clean and ready for new input
										$theSelectedArray = array(); // holds the options from $theChoices that the user actually chose (checked items)
										$innerLoopCount = 1;
										foreach($getSelected2 AS $theSelected)
										{
											$colonAt = strpos($theSelected->value, ":");
											if ($colonAt != FALSE)
											{
												$theSelectedValue = substr($theSelected->value, 0, strlen($theSelected->value) - (strlen($theSelected->value) - $colonAt));
											}
											else
											{
												$theSelectedValue = $theSelected->value;
											}
											
											array_push($theSelectedArray, $theSelectedValue);
											$innerLoopCount++;
										}
										
										$theSelectedArray = array_map(trim, $theSelectedArray);
										
										$hidden_forTest_id = "theSelectedArray_count_".$lead_id."-".$field['id'];
										$hidden_forTest_value = count($theSelectedArray);
										
										?>
											<input type="hidden" id="<?php print $hidden_forTest_id; ?>" value="<?php print $hidden_forTest_value; ?>" class="rt-test-01b" />
										<?php
									} // if it is false, do nothing
									else
									{
										// this whole else if for texting only
										$hidden_forTest_id = "theSelectedArray_count_".$lead_id."-".$field['id'];
										$hidden_forTest_value = count($theSelectedArray);
										
										?>
											<input type="hidden" id="<?php print $hidden_forTest_id; ?>" value="<?php print $hidden_forTest_value; ?>" class="rt-test-02" />
										<?php
									}
									
								} // end multi-selection if/else - the outside one that really does work with multiples
									
								for($a = 1; $a <= count($theChoices); $a++)
								{
									// get the categories 'term_id'
									/* don't think I need this
									foreach($categories AS $category)
									{
										if($category->name == $theChoices[$a]['text'])
										{
											$elementValue = $category->term_id;
										}
									}*/
									
									// check to see if this value is in the $multi array
									if(is_array($theSelectedArray) && in_array($theChoices[$a]["text"], $theSelectedArray))
									{
										switch($field['type'])
										{
											case "radio":
												?>
													<li class="gchoice_<?php print $form_id; ?>_<?php print $field['id']; ?>_<?php print $a; ?>">
														<input id="choice_<?php print $form_id; ?>_<?php print $field['id']; ?>_<?php print $a; ?>" name="input_<?php print $field['id']; ?>.<?php print $a; ?>" type="radio" value="<?php print $theChoices[$a]['value']; ?>" tabindex="<?php print $tabCount; ?>" checked="checked" />
														<label id="label_<?php print $form_id; ?>_<?php print $field['id']; ?>_<?php print $a; ?>" for="choice_<?php print $form_id; ?>_<?php print $field['id']; ?>_<?php print $a; ?>"><?php print $theChoices[$a]['text']; ?></label>
													</li>
												<?php
											break;
											
											case "checkbox":
											case "post_category":
												?>
													<li class="gchoice_<?php print $form_id; ?>_<?php print $field['id']; ?>_<?php print $a; ?>">
														<input id="choice_<?php print $form_id; ?>_<?php print $field['id']; ?>_<?php print $a; ?>" name="input_<?php print $field['id']; ?>.<?php print $a; ?>" type="checkbox" value="<?php print $theChoices[$a]['value']; ?>" tabindex="<?php print $tabCount; ?>" checked="checked" />
														<label id="label_<?php print $form_id; ?>_<?php print $field['id']; ?>_<?php print $a; ?>" for="choice_<?php print $form_id; ?>_<?php print $field['id']; ?>_<?php print $a; ?>"><?php print $theChoices[$a]['text']; ?></label>
													</li>
												<?php
											break;
											
											case "select":
											case "multiselect":
												?>
														<option value="<?php print $theChoices[$a]['value']; ?>" selected><?php print $theChoices[$a]['text']; ?></option>
												<?php
											break;
										} // end inner switch for multi-value fields, selected by user in form (when creating entry)
									}
									else
									{
										switch($field['type'])
										{
											case "radio":
												?>
													<li class="gchoice_<?php print $form_id; ?>_<?php print $field['id']; ?>_<?php print $a; ?>">
														<input id="choice_<?php print $form_id; ?>_<?php print $field['id']; ?>_<?php print $a; ?>" name="input_<?php print $field['id']; ?>.<?php print $a; ?>" type="radio" value="<?php print $theChoices[$a]['value']; ?>" tabindex="<?php print $tabCount; ?>" />
														<label id="label_<?php print $form_id; ?>_<?php print $field['id']; ?>_<?php print $a; ?>" for="choice_<?php print $form_id; ?>_<?php print $field['id']; ?>_<?php print $a; ?>"><?php print $theChoices[$a]['text']; ?></label>
													</li>
												<?php
											break;
											
											case "checkbox":
											case "post_category":
												?>
													<li class="gchoice_<?php print $form_id; ?>_<?php print $field['id']; ?>_<?php print $a; ?>">
														<input id="choice_<?php print $form_id; ?>_<?php print $field['id']; ?>_<?php print $a; ?>" name="input_<?php print $field['id']; ?>.<?php print $a; ?>" type="checkbox" value="<?php print $theChoices[$a]['value']; ?>" tabindex="<?php print $tabCount; ?>" class="rt-else" />
														<label id="label_<?php print $form_id; ?>_<?php print $field['id']; ?>_<?php print $a; ?>" for="choice_<?php print $form_id; ?>_<?php print $field['id']; ?>_<?php print $a; ?>"><?php print $theChoices[$a]['text']; ?></label>
													</li>
												<?php
											break;
											case "select":
											case "multiselect":
												?>
														<option value="<?php print $theChoices[$a]['value']; ?>" selected><?php print $theChoices[$a]['text']; ?></option>
												<?php
											break;
										} // end inner switch for multi-value fields - not selected in form
									}
										
									$tabCount++;
								} // end 'for' that loops through all values for this multi item field.
							
							if($field['type'] == "select" || $field['type'] == "multiselect")
							{
								?>
									</select>
								<?php
							}
								?>
											</ul>
										</div>
									</li>
								<?php
						break;
						
						case "hidden":
					?>
						<input type="hidden" id="input_<?php print $form_id; ?>_<?php print $field['id']; ?>" name="input_<?php print $field['id']; ?>" value="<?php print $field['value']; ?>" />
					<?php
						break;
						
						case "date":
					?>
						<li id="field_<?php print $form_id; ?>_<?php print $field['id']; ?>" class="gfield">
							<label class="gfield_label" for="input_<?php print $form_id; ?>_<?php print $field['id']; ?>"><?php print $field['label']; ?> </label>
							<div class="ginput_container">
								<input id="input_<?php print $form_id; ?>_<?php print $field['id']; ?>" name="input_<?php print $field['id']; ?>" class="datepicker <?php print $field['size']; ?>" type="text" tabindex="<?php print $tabCount; ?>" value="<?php print $lead[$field['id']]; ?>" />
							</div>
						</li>
					<?php
							$tabCount++;
						break;
					} // end switch for type
					
				}
			?>
					</ul>
					<!-- for testing -->
					
					<!-- end testing -->
				</div>
			<?php
			}
		}
		
		
	} // end display_resources_page_meta_box function
	
	function save_resources_page_fields($resources_page_id, $resources_page)
	{
		global $wpdb;
		
		// only wnat this to run if the admin is saving a resources page
		if($resources_page->post_type == "resources_pages")
		{
			// need these values in multiple places
			$assembledPostBody = "";
			$postTitle = "";
			
			// will need the category id's and names for category list saved (for the radio buttons for example)
			$catArgs = array	(	"orderby" 	=> "term_id",
								"order"		=> "ASC",
								"hide_empty"	=> 0,
							);
			$categories = get_categories($catArgs); 
			
			$getStatement = "SELECT * FROM wp_rg_lead WHERE post_id = ".$resources_page_id;
			$getLeadInfo = $wpdb->get_results($getStatement);
			
			if ($getLeadInfo != FALSE)
			{
				foreach ($getLeadInfo AS $theLeadInfo)
				{
					$lead_id = $theLeadInfo->id; // aka the entry number
					$form_id = $theLeadInfo->form_id; // form used to create the entry
					$post_id = $theLeadInfo->post_id;
				}
			} // end if that populates id's

			// test for deleting all fields with pattern xx.x number values
			$decimal_3_1_statement = "DELETE FROM wp_rg_lead_detail WHERE lead_id = $lead_id AND field_number LIKE '%._'";
			$getDecimal_3_1 = $wpdb->get_results($decimal_3_1_statement);
			/*
			if ($getDecimal_3_1 != FALSE)
			{
				foreach ($getDecimal_3_1 AS $theDecimal_3_1)
				{
					echo "The ID: ".$theDecimal_3_1->id." the field number: ".$theDecimal_3_1->field_number."<br />";
				}
			}
			*/
			// end test for xx.x number values
			
			foreach($_POST AS $key => $value)
			{
				// strip the $key (the field name) down to just the number. that number is then the 'field_number' in wp_rg_lead_detail.
				// for testing -> echo "The key: ".$key."<br />";
				$startAt = strpos($key, "_");
				$theFieldNum = substr($key, ($startAt + 1));
				// for testing -> echo "Substring value: ".$theFieldNum."<br />";
				// figured out that wordpress changes the "." in my form names to "_", change them back
				$theFieldNum = str_replace("_", ".", $theFieldNum);
				// for testing -> echo "Pre-float value: ".$theFieldNum."<br />";
				$theFieldNum = floatval($theFieldNum); // must be a float for insert into db
                    // for testing -> echo "Float value: ".$theFieldNum."<br />";
				
				if (strlen(trim($value)) > 0)
				{
					// if the field number has a "." then we need the category name for its value
					if (strpos($theFieldNum, ".") !== FALSE)
					{
                              // do this first, in case the field is not a category, for example, the 'multiple day event' checkbox on the conference form
                              $theValue = $value;
                              
						// for testing -> echo "Total number of categories returned: ".count($categories)."<br />";
						foreach($categories AS $category)
						{
                                   if($category->term_id == $value)
							{
								// 03-30-2018 just pass the 'name' not the number -> $theValue = $category->name.":".$value;
								$theValue = $category->name;
                                   }
                              }
                              
                              // for testing -> echo "Checking category... term id: ".$category->term_id." field number: ".$theFieldNum." and passed value: ".$value."<br />";
					}
					else
					{
						// may have to put some field cleanup code in here.
						$theValue = $value;
                              // for testing -> echo "the field number: ".$theFieldNum." and the value: ".$theValue."<br />";
					}
					
					// add the value to the 'post_content' value, unless it is the title -> $theFieldNum = 1
					if($theFieldNum === 1)
					{
						$thePostTitle = $theValue;
					}
					elseif ($theFieldNum > 0)
					{
						$thePostContent .= $theValue."\r\n";
					}
					
					if($theFieldNum != FALSE)
					{
						// for testing -> echo "Field Number is ".$theFieldNum." and value is ".$theValue."<br />";
						rt_update_resources_entry_field($theFieldNum, $theValue, $lead_id, $form_id);
					}
				}
				else
				{
					// send this to the delete function, just in case the field currently has data and the user deleted it.
					if($theFieldNum != FALSE)
					{
						// this will only be used if you need to do something special if a field is empty.
						// the $wpdb->replace should remove any fields that have no data
					}
					
				}
			} // end the foreach $_POST
			
			// this will update the wp_posts record for this resource entry
			$contentArray = array(	"ID" 		=> $resources_page_id,
								"post_content" => $thePostContent,
								"post_title"	=> $thePostTitle);
			
			// to keep from having an infinite loop
			remove_action("save_post", "save_resources_page_fields");
			
			// do my custom save of post data
			rt_do_resources2posts($lead_id, $post_id, $form_id);
			
			// add save_post hook again
			add_action("save_post", "save_resources_page_fields", 10, 2);
		} // end check of resources_pages post_type
	} // end save_resources_page_fields function
	
	add_action("save_post", "save_resources_page_fields", 10, 2);
	
	function in_array_rt($needle, $haystack, $strict = FALSE)
	{
		foreach ($haystack AS $item)
		{
			if (($strict ? $item === $needle : $item == $needle) || (is_array($item) && in_array_rt($needle, $item, $strict)))
			{
				return TRUE;
			}
		}
		
		return FALSE;
	}
	
	function rt_update_resources_entry_field($theFieldNum, $theValue, $theLeadId, $theFormId)
	{
		global $wpdb;
		
		// 03-30-2018 this is being added to save large values to the "wp_rg_lead_detail_long" table
		// this array holds the form numbers and their field id numbers that should be saved to the 'long' table
		$longSaveArray = array(	7	=>		array(	8,
												9),
							8	=>		2,
							10	=>		array(	2,
												4,
												5),
							9	=>		2,
							4	=>		15,
							2	=>		3);
		
		// 03-30-2018 becauses of changes made to the forms after the resources were mass-imported, the value of $theFieldNum WILL NOT match original form field values (in xx.y, the xx is the same, y is not)
		// so, must do this (not pretty, maybe. but easiest)
		if (strpos($theFieldNum, ".") === FALSE)
		{
			$getIdStatement = "SELECT * FROM wp_rg_lead_detail WHERE lead_id = ".$theLeadId." AND form_id = ".$theFormId." AND CAST(field_number AS DECIMAL(3,1)) = CAST(".$theFieldNum." AS DECIMAL(3,1))";
		}
		else
		{
			$getIdStatement = "SELECT * FROM wp_rg_lead_detail WHERE lead_id = ".$theLeadId." AND form_id = ".$theFormId." AND value = ".$theValue;
		}
		// for testing -> echo $getIdStatement."<br />";
		$getId = $wpdb->get_results($getIdStatement);
		
		if($getId != FALSE)
		{
			foreach($getId AS $theId)
			{
				$recordId = $theId->id;
				// for testing -> 	echo "The record id: ".$recordId."<br />";
			}
		}
		else
		{
			$recordId = ""; // hopefully for new records this will cause auto-incrementing of the index (id) field.
			// for testing -> echo "The record id: not returned<br />";
		}
		
		// for testing -> echo "The lead id: ".$theLeadId." The form id: ".$theFormId." The field id: ".$theFieldNum.". The value: ".$theValue."<br /><br />";
		
		if($theFieldNum > 0) // if($recordId > 0) this is a test, where is the 0 wp_rg_lead_detail.id coming from??
		{
			$dbTable = "wp_rg_lead_detail";
			$theData = array	(	
								"id"			=> $recordId,
								"lead_id"		=> $theLeadId,
								"form_id"		=> $theFormId,
								"field_number"	=> $theFieldNum,
								"value"		=> $theValue
							);
			$theFormats = array	(	
								"%d",
								"%d",
								"%d",
								"%f",
								"%s"
							);
			
			$wpdb->replace($dbTable, $theData, $theFormats);
		
			// check to see if form is in array (which means it has a long field) - note: at the time of coding all forms have a long field, but this may not always be so
			if (array_key_exists($theFormId, $longSaveArray))
			{
				// for testing ->	print "The key is in the array: ".$theFormId."<br />";
				
				if(is_array($longSaveArray[$theFormId]) && in_array($theFieldNum, $longSaveArray[$theFormId]))
				{
					// the form has more than one long value
					foreach($longSaveArray[$theFormId] AS $value)
					{
						$dbTable2 = "wp_rg_lead_detail_long";
						$theData2 = array	(	
											"lead_detail_id" 	=> $recordId,
											"value"			=> $theValue
										);
						$theFormats2 = array	(	
											"%d",
											"%s"
										);
						
						$wpdb->replace($dbTable2, $theData2, $theFormats2);
					}
				}
				elseif ($longSaveArray[$theFormId] = $theFieldNum)
				{
					// the form has only one long value
					$dbTable2 = "wp_rg_lead_detail_long";
					$theData2 = array	(	
										"lead_detail_id" 	=> $recordId,
										"value"			=> $theValue
									);
					$theFormats2 = array	(	
										"%d",
										"%s"
									);
					
					$wpdb->replace($dbTable2, $theData2, $theFormats2);
				}
			} // end if checking if form id is in $longSaveArray
		} // end if to check for $recordId > 0
	} // end rt_update_resources_entry_field functioin
?>

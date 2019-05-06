<?php
	// function to update the wp_posts table after a resource has been edited in the Resources Management plugin.
	// this code was addapted from my code that originally added the resources to the wp_posts table after mass-import.
	// resources data is saved in the wp_rg_* tables because it is entered through a Gravity Forms form.
	
	function rt_do_resources2posts($lead_id, $post_id, $form_id)
	{
		global $wpdb;
		$printThis = "";
		$successCount = 0;
		$failCount = 0;
		$insertPost = array();
		$formId = $form_id; // this is easier than going through the code and finding all instances of $formId and changing them.
		
		// this is from the original insert function. it may be needed later, keep.  (also good for referencing which form ids go with which forms)
		$formIdArray = array(	2, // organizations
							4, //literature
							7, // conferences
							8, // degree programs
							9, // listserv
							10); // ind. practitioners
		
		// because I didn't enter the information for the categories into the import cvs files as 'catName:catNum' I have to do this
		$artsCatsArray = array(	"arts"			=> 27,
							"dance/movement" 	=> 238,
							"drumming"		=> 26,
							"mixed-art forms"	=> 53,
							"music"			=> 31,
							"theater arts"		=> 55,
							"writing"			=> 29);
		
		$regionsCatsArray = array(	"southern california"	=> 57,
								"northern california"	=> 58,
								"western us"			=> 59,
								"central us"			=> 60,
								"eastern us"			=> 61,
								"national"			=> 62,
								"international"		=> 63);
		
		$populationCatsArray = array(	"very young children"	=> 64,
								"children"			=> 239,
								"adolescents"			=> 240,
								"adults"				=> 67,
								"older adults"			=> 68,
								"special needs"		=> 36);
		
		$mainCatsArray = array(	"organizations"			=> 79,
							"individual practitioners"	=> 172,
							"degree programs"			=> 136,
							"listservs"				=> 151,
							"conferences"				=> 121,
							"literature"				=> 100);
		
		$litCatsArray = array(	"book"				=> 70,
							"book chapter"			=> 71,
							"journal"				=> 72,
							"journal article"		=> 73,
							"report"				=> 74,
							"website"				=> 75,
							"other literature type"	=> 241);
		
		$metaValueArray = array(	"_gform-entry-id" 		=> 0, // this will be filled programatically
							"_gform-form-id"		=> 0, // this will be filled programatically
							"_edit_lock"			=> "", // this is the unix time : author id
							"_format_gallery"		=> "", // this should stay blank
							"_format_audio_embed"	=> "", // this should stay blank
							"_format_video_embed"	=> "", // this should stay blank
							"_edit_last"			=> 3, // author id
							"MultipleSidebars"		=> "multiplesidebars4427",
							"_post_align"			=> "media-right",
							"_noindex"			=> 0);
		
		
		switch ($formId)
		{
			case 2: // organizations
				// reset variables
				$insertTitle = "";
				$insertContent = "";
				unset($insertCats);
				$insertCats = array();
				unset($allCatsArray);
				$allCatsArray = array();
				$allCatsArray[] = 79; // the first category to place in the array... the resource category
				
				//print "Key: ".$key." => Value: ".$value."<br />";
				$formEntryId = $lead_id;
				$printThis .= "Form Entry ID: ".$formEntryId."<br />";
				$allFormData = $wpdb->get_results("SELECT * FROM wp_rg_lead_detail WHERE lead_id = $formEntryId", ARRAY_A);
				
				if (!empty($allFormData))
				{
					foreach ($allFormData AS $formData)
					{
						if ($formData[field_number] == 1) $theOrganization = $formData[value];
						if ($formData[field_number] == 3) $theMission = $formData[value];
						if ($formData[field_number] == 3) $theMissionId = $formData[id]; // this is needed to check to see if there is a long value for description in 'wp_rg_lead_detail_long'
						if ($formData[field_number] == 5) $thePhone = $formData[value];
						if ($formData[field_number] == 6) $theWebAddress = $formData[value];
						if ($formData[field_number] == 7) $theEmail = $formData[value];
						if ($formData[field_number] == 54) $theAddress1 = $formData[value];
						if ($formData[field_number] == 55) $theAddress2 = $formData[id];
						if ($formData[field_number] == 56) $theCity = $formData[value];
						if ($formData[field_number] == 57) $theState = $formData[value];
						if ($formData[field_number] == 58) $theZip = $formData[value];
						
						if ($formData[field_number] >= 47 && $formData[field_number] < 48)
						{
							$length = strlen($formData[value]);
							$findEnd = strpos($formData[value], ":");
							if ($findEnd != FALSE)
							{
								$endPos = $length - $findEnd;
								$pre = substr($formData[value], 0, -$endPos);
								$theArtCats .= $pre."<br />";
							}
							else
							{
								$pre = trim($formData[value]);
								$theArtCats .= $pre."<br />";
							}
							
							if (array_key_exists($pre, $artsCatsArray))
							{
								$allCatsArray[] = $artsCatsArray[$pre];
							}
						}
						if ($formData[field_number] >= 48 && $formData[field_number] < 49)
						{
							$length = strlen($formData[value]);
							$findEnd = strpos($formData[value], ":");
							if ($findEnd != FALSE)
							{
								$endPos = $length - $findEnd;
								$pre = substr($formData[value], 0, -$endPos);
								$theRegionCats .= $pre."<br />";
							}
							else
							{
								$pre = trim($formData[value]);
								$theRegionCats .= $pre."<br />";
							}
							
							if (array_key_exists($pre, $regionsCatsArray))
							{
								$allCatsArray[] = $regionsCatsArray[$pre];
							}
						}
						if ($formData[field_number] >= 49 && $formData[field_number] < 50)
						{
							$length = strlen($formData[value]);
							$findEnd = strpos($formData[value], ":");
							if ($findEnd != FALSE)
							{
								$endPos = $length - $findEnd;
								$pre = substr($formData[value], 0, -$endPos);
								$theGroupCats .= $pre."<br />";
							}
							else
							{
								$pre = trim($formData[value]);
								$theGroupCats .= $pre."<br />";
							}
							
							if (array_key_exists($pre, $populationCatsArray))
							{
								$allCatsArray[] = $populationCatsArray[$pre];
							}
						}
						
						if ($formData[field_number] == 50) $theSubmitter = $formData[value];
						if ($formData[field_number] == 51) $theSubmitterEmail = $formData[value];
						if ($formData[field_number] == 52) $theSubmitterPhone = $formData[value];
						
						
					} // end foreach with individual entry id's
				}
				else
				{
					$printThis = "No data for form entry ".$formEntryId."<br />";
				}
				
				// check to see if there are long versions of the info entered for 'description' and 'who should attend'
				$missionData = $wpdb->get_results("SELECT * FROM wp_rg_lead_detail_long WHERE lead_detail_id = $theMissionId", ARRAY_A);
				
				if (!empty($missionData))
				{
					foreach ($missionData AS $theMissionData)
					{
						$theMission = $theMissionData[value];
					}
				}
				
				// assemble address
				$assembledAddress = "";
				if (!empty($theAddress1)) $assembledAddress .= $theAddress1."<br />";
				if (!empty($theAddress2)) $assembledAddress .= $theAddress2."<br />";
				if (!empty($theCity)) $assembledAddress .= $theCity;
				if (!empty($theCity) && !empty($theState)) $assembledAddress .= ", ";
				if (!empty($theState)) $assembledAddress .= $theState."<br />";
				if (!empty($theZip)) $assembledAddress .= $theZip;
				
				// now do the contact table data - this has to be done because if there is no address, for example, the email needs to move into cell 1, then phone into cell 2, and  website to cell 3.  cell 4 would be blank
				$contactArray = array();
				$contactArrayIndex = 0;
				
				if (!empty($assembledAddress)) :
					$contactArray[$contactArrayIndex]['title'] = "address";
					$contactArray[$contactArrayIndex]['value'] = $assembledAddress;
					$contactArrayIndex++;
				endif;
				
				if (!empty($theEmail)) :
					$contactArray[$contactArrayIndex]['title'] = "email";
					$contactArray[$contactArrayIndex]['value'] = $theEmail;
					$contactArrayIndex++;
				endif;
				
				if (!empty($thePhone)) :
					$contactArray[$contactArrayIndex]['title'] = "phone";
					$contactArray[$contactArrayIndex]['value'] = $thePhone;
					$contactArrayIndex++;
				endif;
				
				if (!empty($theWebAddress)) :
					$contactArray[$contactArrayIndex]['title'] = "website";
					$contactArray[$contactArrayIndex]['value'] = $theWebAddress;
				endif;
				
				// begin the actual layout output
				$insertContent .= 	"<div id=\"resources_title\">".$theOrganization."</div>
								<br />
								<h3 class=\"yellow\">mission:</h3>
								".$theMission."<br />
								<br />";
				
				if (!empty($contactArray))
				{
					if (count($contactArray) == 1) :
						$insertContent .= "	<h3 class=\"yellow\">".$contactArray[0]['title']."</h3>
										".$contactArray[0]['value']."<br />";
					else :
						$insertContent .= "	<table style=\"border-collapse: collapse; width: 100%;\">
											<tr>";
						
						for ($x = 0; $x < count($contactArray); $x++)
						{
							$insertContent .= "		<td style=\"width: 50%; padding-bottom: 1em;\">
													<h3 class=\"yellow\">".$contactArray[$x]['title'].":</h3>
													".$contactArray[$x]['value']."
												</td>";
							
							if ($x == 1) $insertContent .= "</tr><tr>";
						}
						
						// if count is exactly 3, then the last cell needs to be added and empty
						if (count($contactArray) == 3) :
							$insertContent .= "	<td style=\"width: 50%; padding-bottom: 1em;\">
												&nbsp;
											</td>";
						endif;
						
						$insertContent .= "		</tr>
										</table>";
					
					endif;
					
				} // end address, email, phone, web address if
				
				$insertTitle = $theOrganization;
				$insertCats = $allCatsArray;
				$insertExcerpt = $theMission;
			break;
			
			case 4: // literature
				// reset variables
				$insertTitle = "";
				$insertContent = "";
				unset($insertCats);
				$insertCats = array();
				unset($allCatsArray);
				$allCatsArray = array();
				$allCatsArray[] = 100; // first thing to put in the cats array is the resource category
				
				//print "Key: ".$key." => Value: ".$value."<br />";
				$formEntryId = $lead_id;
				$printThis .= "Form Entry ID: ".$formEntryId."<br />";
				$allFormData = $wpdb->get_results("SELECT * FROM wp_rg_lead_detail WHERE lead_id = $formEntryId", ARRAY_A);
				
				if (!empty($allFormData))
				{
					foreach ($allFormData AS $formData)
					{
						if ($formData[field_number] == 20) $theTitle = $formData[value];
						if ($formData[field_number] == 26) $theReference = $formData[value];
						if ($formData[field_number] == 15) $theDescription = $formData[value];
						if ($formData[field_number] == 15) $theDescriptionId = $formData[id]; // this is needed to check to see if there is a long value for description in 'wp_rg_lead_detail_long'
						if ($formData[field_number] == 16) $theWebsite = $formData[value];
						
						if ($formData[field_number] >= 18 && $formData[field_number] < 19)
						{
							$length = strlen($formData[value]);
							$findEnd = strpos($formData[value], ":");
							if ($findEnd != FALSE)
							{
								$endPos = $length - $findEnd;
								$pre = substr($formData[value], 0, -$endPos);
								$theArtCats .= $pre."<br />";
							}
							else
							{
								$pre = trim($formData[value]);
								$theArtCats .= $pre."<br />";
							}
							
							if (array_key_exists($pre, $artsCatsArray))
							{
								$allCatsArray[] = $artsCatsArray[$pre];
							}
						}
						if ($formData[field_number] >= 17 && $formData[field_number] < 18)
						{
							$length = strlen($formData[value]);
							$findEnd = strpos($formData[value], ":");
							if ($findEnd != FALSE)
							{
								$endPos = $length - $findEnd;
								$pre = substr($formData[value], 0, -$endPos);
								$thePopulationCats .= $pre."<br />";
							}
							else
							{
								$pre = trim($formData[value]);
								$thePopulationCats .= $pre."<br />";
							}
							
							if (array_key_exists($pre, $populationCatsArray))
							{
								$allCatsArray[] = $populationCatsArray[$pre];
							}
						}
						if ($formData[field_number] >= 14 && $formData[field_number] < 15)
						{
							$length = strlen($formData[value]);
							$findEnd = strpos($formData[value], ":");
							if ($findEnd != FALSE)
							{
								$endPos = $length - $findEnd;
								$pre = substr($formData[value], 0, -$endPos);
								$theLiteratureType .= $pre."<br />";
							}
							else
							{
								$pre = trim($formData[value]);
								$theLituratureType .= $pre."<br />";
							}
							
							if (array_key_exists($pre, $litCatsArray))
							{
								$allCatsArray[] = $theLiteratureType[$pre];
							}
						}
					} // end $allFormData foreach
				}
				else
				{
					// nothing returned from wp_rg_lead_detail
					$printThis .= "No data for form entry ".$formEntryId."<br />";
				} // if/else for form data returned
				
				// check to see if there are long versions of the info entered for 'description'
				$descriptionData = $wpdb->get_results("SELECT * FROM wp_rg_lead_detail_long WHERE lead_detail_id = $theDescriptionId", ARRAY_A);
				
				if (!empty($descriptionData))
				{
					foreach ($descriptionData AS $theDescriptionData)
					{
						$theDescription = $theDescriptionData[value];
					}
				}
					
				$insertContent .= 	"<div id=\"resources_title\">".$theTitle."</div>
								<br />
								<br />
								<h3 class=\"yellow\">literature type:</h3>";
				
				if (!empty($theLiteratureType)) $insertContent .=	$theLiteratureType."<br /><br />";
				
				$insertContent .=	"<h3 class=\"yellow\">reference:</h3>";
				
				if (!empty($theReference)) $insertContent .=	$theReference."<br /><br />";
				
				$insertContent .=	"<h3 class=\"yellow\">description:</h3>";
				
				if (!empty($theDescription)) $insertContent .=	$theDescription."<br /><br />";
				
				$insertContent .=	"<h3 class=\"yellow\">website:</h3>";
				
				if (!empty($theWebsite)) $insertContent .=	"<a href=\"".$theWebsite."\" target=\"_blank\">".$theWebsite."</a><br /><br />";
				
				$insertContent .= "	<table style=\"border-collapse: collapse; width: 100%;\">
									<tr>
										<td style=\"width: 50%;\">
											<h3 class=\"yellow\">arts categories:</h3>";
				
				$insertContent .= (!empty($theArtCats)) ? $theArtCats : "&nbsp;";
				
				$insertContent .= "			</td>
										<td style=\"width: 50%;\">
											<h3 class=\"yellow\">population(s) served:</h3>";
				
				$insertContent .= (!empty($thePopulationCats)) ? $thePopulationCats : "&nbsp;";
				
				$insertContent .= "			</td>
									</tr>
								</table>";
				
				$insertTitle = $theTitle;
				$insertCats = $allCatsArray;
				$insertExcerpt = $theDescription;
			break;
			
			case 7: // conferences
				// reset variables
				$insertTitle = "";
				$insertContent = "";
				unset($insertCats);
				$insertCats = array();
				unset($allCatsArray);
				$allCatsArray = array();
				$allCatsArray[] = 121; // first thing to put in the cats array is the resource category
				
				//print "Key: ".$key." => Value: ".$value."<br />";
				$formEntryId = $lead_id;
				$printThis .= "Form Entry ID: ".$formEntryId."<br />";
				$allFormData = $wpdb->get_results("SELECT * FROM wp_rg_lead_detail WHERE lead_id = $formEntryId", ARRAY_A);
				
				if (!empty($allFormData))
				{
					foreach ($allFormData AS $formData)
					{
						if ($formData[field_number] == 1) $theTitle = $formData[value];
						if ($formData[field_number] == 2) $theAffiliation = $formData[value];
						if ($formData[field_number] == 3) $theStartDate = $formData[value];
						if ($formData[field_number] >= 4 && $formData[field_number] < 5) $theMultiDay = $formData[value];
						if ($formData[field_number] == 5) $theEndDate = $formData[value];
						if ($formData[field_number] == 6) $theCity = $formData[value];
						if ($formData[field_number] == 7) $theState = $formData[value];
						if ($formData[field_number] == 8) $theDescription = $formData[value];
						if ($formData[field_number] == 8) $theDescriptionId = $formData[id]; // this is needed to check to see if there is a long value for description in 'wp_rg_lead_detail_long'
						if ($formData[field_number] == 9) $theAttend = $formData[value];
						if ($formData[field_number] == 9) $theAttendId = $formData[id]; // same as two lines above, but for attendies
						if ($formData[field_number] == 11) $thePhone = $formData[value];
						if ($formData[field_number] == 12) $theEmail = $formData[value];
						if ($formData[field_number] == 13) $theWebAddress = $formData[value];
						
						// remove the "/n"s from the attend list
						$theAttend = str_replace("\n", '', $theAttend);
						
						if ($formData[field_number] >= 14 && $formData[field_number] < 15)
						{
							$length = strlen($formData[value]);
							$findEnd = strpos($formData[value], ":");
							if ($findEnd != FALSE)
							{
								$endPos = $length - $findEnd;
								$pre = substr($formData[value], 0, -$endPos);
								$theArtCats .= $pre."<br />";
							}
							else
							{
								$pre = trim($formData[value]);
								$theArtCats .= $pre."<br />";
							}
							
							if (array_key_exists($pre, $artsCatsArray))
							{
								$allCatsArray[] = $artsCatsArray[$pre];
							}
						}
						if ($formData[field_number] >= 15 && $formData[field_number] < 16)
						{
							$length = strlen($formData[value]);
							$findEnd = strpos($formData[value], ":");
							if ($findEnd != FALSE)
							{
								$endPos = $length - $findEnd;
								$pre = substr($formData[value], 0, -$endPos);
								$theRegionCats .= $pre."<br />";
							}
							else
							{
								$pre = trim($formData[value]);
								$theRegionCats .= $pre."<br />";
							}
							
							if (array_key_exists($pre, $regionsCatsArray))
							{
								$allCatsArray[] = $regionsCatsArray[$pre];
							}
						}
						
						if ($formData[field_number] == 16) $theSubmitter = $formData[value];
						if ($formData[field_number] == 17) $theSubmitterEmail = $formData[value];
						if ($formData[field_number] == 18) $theSubmitterPhone = $formData[value];
						if ($formData[field_number] == 21) $theZip = $formData[value];
					}
				}
				else
				{
					$printThis = "No data for form entry ".$formEntryId."<br />";
				}
				
				// check to see if there are long versions of the info entered for 'description' and 'who should attend'
				$descriptionData = $wpdb->get_results("SELECT * FROM wp_rg_lead_detail_long WHERE lead_detail_id = $theDescriptionId", ARRAY_A);
				
				if (!empty($descriptionData))
				{
					foreach ($descriptionData AS $theDescriptionData)
					{
						$theDescription = $theDescriptionData[value];
					}
				}
				
				$attendData = $wpdb->get_results("SELECT * FROM wp_rg_lead_detail_long WHERE lead_detail_id = $theAttendId", ARRAY_A);
				
				if (!empty($attendData))
				{
					foreach ($attendData AS $theAttendData)
					{
						$theAttend = $theAttendData[value];
						$theAttend = str_replace("\n", '', $theAttend);
					}
				}
					
				$insertContent .= 	"<div id=\"resources_title\">".$theTitle."</div>
								<br />";
				
				(isset($theStartDate) && !empty($theStartDate)) ? $newStartDate = rt_convert_date($theStartDate) : $newStartDate = "";
				(isset($theEndDate) && !empty($theEndDate)) ? $newEndDate = rt_convert_date($theEndDate) : $newEndDate = "";
					
				if ($theMultiDay == "yes")
				{
					$theDates = $newStartDate." - ".$newEndDate;
				}
				else
				{
					$theDates = $newStartDate;
				}
				
				$dateExists = (strlen(trim($theDates)) > 0) ? true : false;
				$cityExists = (strlen(trim($theCity)) > 0) ? true : false;
				$stateExists = (strlen(trim($theState)) > 0) ? true : false;
				
				if ($dateExists)
				{
					$insertContent .=	"<table style=\"border-collapse: collapse; width: 100%;\">
									<tr>
										<td style=\"width: 50%;\">
											<h3 class=\"yellow\">date(s):</h3>";
												if (!empty($theDates)) : $insertContent .= $theDates; endif;
					$insertContent .=	"		</td>
										<td style=\"width: 50%;\">";
									
					if ($cityExists && $stateExists)
					{
						$insertContent .= "			<h3 class=\"yellow\">location:</h3>
												".$theCity.", ".$theState."
											</td>";
					}
					elseif ($cityExists || $stateExists)
					{
						$insertContent .= "			<h3 class=\"yellow\">location:</h3>
												".$theCity.$theState."
											</td>";
					}
					else
					{
						$insertContent .= "			&nbsp;
											</td>";
					}
					
					$insertContent .= "		</tr>
									</table>
									<br />";
				}
				elseif (!$dateExists && ($cityExists || $stateExists))
				{
					$insertContent .=	"<table style=\"border-collapse: collapse; width: 100%;\">
									<tr>
										<td style=\"width: 50%;\">
											<h3 class=\"yellow\">location:</h3>";
										
					if ($cityExists && $stateExists)
					{
						$insertContent .=				$theCity.", ".$theState;
					}
					else
					{
						$insertContent .=				$theCity.$theState; // the one that exists will show up
					}
					
					$insertContent .= "			</td>
											<td style=\"width: 50%;\">
												&nbsp;
											</td>
										</tr>
									</table>
									<br />";
				}
				
				$insertContent .=	"<h3 class=\"yellow\">description:</h3>";
				if (!empty($theDescription))
				{
					$insertContent .= 		$theDescription."<br /><br />";
				}
				
				$insertContent .= "	<h3 class=\"yellow\">who should attend:</h3>";
				if (!empty($theAttend))
				{
					$insertContent .=	$theAttend."<br />
								<br />";
				}
				
				$insertContent .= "	<h3 class=\"yellow\">sponsoring organization(s):</h3>";
				if (!empty($theAffiliation))
				{
					$insertContent .=	$theAffiliation."<br />";
					
					if (!empty($thePhone))
					{
						$insertContent .= $thePhone."<br />";
					}
					
					if (!empty($theEmail))
					{
						$insertContent .= "	<a href=\"mailto:".$theEmail."\" target=\"_blank\">".$theEmail."</a><br />";
					}
					
					if (!empty($theWebAddress))
					{
						$insertContent .= "	<a href=\"".$theWebAddress."\" target=\"_blank\">".$theWebAddress."</a><br />";
					}
					
					$insertContent .= "	<br />";
				}
				
				$insertContent .= "	<table style=\"border-collapse: collapse; width: 100%;\">
									<tr>
										<td style=\"width: 50%;\">
											<h3 class=\"yellow\">art categories:</h3>";
				
				if (!empty($theArtCats))
				{
					$insertContent .=			$theArtCats;
				}
				else
				{
					$insertContent .= "			&nbsp;";
				}
				
				$insertContent .= "			</td>
										<td style=\"width: 50%;\">
											<h3 class=\"yellow\">regions:</h3>";
				
				if (!empty($theRegionCats))
				{
					$insertContent .= 			$theRegionCats;
				}
				else
				{
					$insertContent .= "			&nbsp;";
				}
				
				$insertContent .= "			</td>
									</tr>
								</table>";
				
				$insertTitle = $theTitle;
				$insertCats = $allCatsArray;
				$insertExcerpt = $theDescription;
			break;
			
			case 8: // degree programs
				// reset variables
				$insertTitle = "";
				$insertContent = "";
				unset($insertCats);
				$insertCats = array();
				unset($allCatsArray);
				$allCatsArray = array();
				$allCatsArray[] = 136; // first thing to put in the cats array is the resource category
				
				//print "Key: ".$key." => Value: ".$value."<br />";
				$formEntryId = $lead_id;
				$printThis .= "Form Entry ID: ".$formEntryId."<br />";
				$allFormData = $wpdb->get_results("SELECT * FROM wp_rg_lead_detail WHERE lead_id = $formEntryId", ARRAY_A);
				
				if (!empty($allFormData))
				{
					foreach ($allFormData AS $formData)
					{
						if ($formData[field_number] == 1) $theTitle = $formData[value];
						if ($formData[field_number] == 2) $theDescription = $formData[value];
						if ($formData[field_number] == 2) $theDescriptionId = $formData[id]; // this is needed to check to see if there is a long value for description in 'wp_rg_lead_detail_long'
						if ($formData[field_number] == 14) $theAddress1 = $formData[value];
						if ($formData[field_number] == 15) $theAddress2 = $formData[value];
						if ($formData[field_number] == 16) $theCity = $formData[value];
						if ($formData[field_number] == 17) $theState = $formData[value];
						if ($formData[field_number] == 18) $theZipCode = $formData[value];
						if ($formData[field_number] == 20) $theCountry = $formData[value];
						if ($formData[field_number] == 4) $thePhone = $formData[value];
						if ($formData[field_number] == 5) $theEmail = $formData[value];
						if ($formData[field_number] == 6) $theWebsite = $formData[value];
						
						if ($formData[field_number] >= 7 && $formData[field_number] < 8)
						{
							$length = strlen($formData[value]);
							$findEnd = strpos($formData[value], ":");
							if ($findEnd != FALSE)
							{
								$endPos = $length - $findEnd;
								$pre = substr($formData[value], 0, -$endPos);
								$theArtCats .= $pre."<br />";
							}
							else
							{
								$pre = trim($formData[value]);
								$theArtCats .= $pre."<br />";
							}
							
							if (array_key_exists($pre, $artsCatsArray))
							{
								$allCatsArray[] = $artsCatsArray[$pre];
							}
						}
						if ($formData[field_number] >= 8 && $formData[field_number] < 9)
						{
							$length = strlen($formData[value]);
							$findEnd = strpos($formData[value], ":");
							if ($findEnd != FALSE)
							{
								$endPos = $length - $findEnd;
								$pre = substr($formData[value], 0, -$endPos);
								$theRegionCats .= $pre."<br />";
							}
							else
							{
								$pre = trim($formData[value]);
								$theRegionCats .= $pre."<br />";
							}
							
							if (array_key_exists($pre, $regionsCatsArray))
							{
								$allCatsArray[] = $regionsCatsArray[$pre];
							}
						}
					} // end of $allFormData foreach
				}
				else
				{
					// nothing returned for this form
					$printThis .= "No data for form entry ".$formEntryId."<br />";
				} // end $allFormData if/else to see if there are data returned
				
				// check to see if there are long versions of the info entered for 'description'
				$descriptionData = $wpdb->get_results("SELECT * FROM wp_rg_lead_detail_long WHERE lead_detail_id = $theDescriptionId", ARRAY_A);
				
				if (!empty($descriptionData))
				{
					foreach ($descriptionData AS $theDescriptionData)
					{
						$theDescription = $theDescriptionData[value];
					}
				}
				
				$insertContent .= 	"<div id=\"resources_title\">".$theTitle."</div>
								<br />
								<br />
								<h3 class=\"yellow\">program description:</h3>";
				
				if (!empty($theDescription)) $insertContent .=	$theDescription."<br /><br />";
				
				$insertContent .=	"<h3 class=\"yellow\">contact:</h3>";
				
				if (!empty($theAddress1)) $insertContent .= $theAddress1."<br />";
				if (!empty($theAddress2)) $insertContent .= $theAddress2."<br />";
				if (!empty($theCity)) $insertContent .= $theCity;
				if (!empty($theState)) $insertContent .= ", ".$theState;
				if (!empty($theZipCode)) $insertContent .= " ".$theZipCode."<br />";
				if (!empty($theCountry)) $insertContent .= $theCountry."<br />";
				
				$insertContent .= "<br />";
				
				if (!empty($theEmail)) $insertContent .= "<a href=\"mailto:".$theEmail."\" target=\"_blank\">".$theEmail."</a><br />";
				if (!empty($thePhone)) $insertContent .= $thePhone."<br />";
				if (!empty($theWebsite)) $insertContent .= "<a href=\"".$theWebsite."\" target=\"_blank\">".$theWebsite."</a><br />";
				
				$insertContent .= "<br />";
				
				$insertContent .= "	<table style=\"border-collapse: collapse; width: 100%;\">
									<tr>
										<td style=\"width: 50%;\">
											<h3 class=\"yellow\">art categories:</h3>";
				
				$insertContent .= (!empty($theArtCats)) ? $theArtCats : "&nbsp;";
				
				$insertContent .= "			</td>
										<td style=\"width: 50%;\">
											<h3 class=\"yellow\">region(s) served:</h3>";
				
				$insertContent .= (!empty($theRegionCats)) ? $theRegionCats : "&nbsp;";
				
				$insertContent .= "			</td>
									</tr>
								</table>";
				
				$insertTitle = $theTitle;
				$insertCats = $allCatsArray;
				$insertExcerpt = $theDescription;
			break;
			
			case 9:  // listservs
				// reset variables
				$insertTitle = "";
				$insertContent = "";
				unset($insertCats);
				$insertCats = array();
				unset($allCatsArray);
				$allCatsArray = array();
				$allCatsArray[] = 151; // first thing to put in the cats array is the resource category
				
				//print "Key: ".$key." => Value: ".$value."<br />";
				$formEntryId = $lead_id;
				$allFormData = $wpdb->get_results("SELECT * FROM wp_rg_lead_detail WHERE lead_id = $formEntryId", ARRAY_A);
				
				if (!empty($allFormData))
				{
					foreach ($allFormData AS $formData)
					{
						if ($formData[field_number] == 1) $theTitle = $formData[value];
						if ($formData[field_number] == 2) $theDescription = $formData[value];
						if ($formData[field_number] == 2) $theDescriptionId = $formData[id]; // this is needed to check to see if there is a long value for description in 'wp_rg_lead_detail_long'
						if ($formData[field_number] == 3) $theHowToJoin = $formData[value];
						
						if ($formData[field_number] >= 4 && $formData[field_number] < 5)
						{
							$length = strlen($formData[value]);
							$findEnd = strpos($formData[value], ":");
							if ($findEnd != FALSE)
							{
								$endPos = $length - $findEnd;
								$pre = substr($formData[value], 0, -$endPos);
								$theArtCats .= $pre."<br />";
							}
							else
							{
								$pre = trim($formData[value]);
								$theArtCats .= $pre."<br />";
							}
							
							if (array_key_exists($pre, $artsCatsArray))
							{
								$allCatsArray[] = $artsCatsArray[$pre];
							}
						}
						if ($formData[field_number] >= 5 && $formData[field_number] < 6)
						{
							$length = strlen($formData[value]);
							$findEnd = strpos($formData[value], ":");
							if ($findEnd != FALSE)
							{
								$endPos = $length - $findEnd;
								$pre = substr($formData[value], 0, -$endPos);
								$theRegionCats .= $pre."<br />";
							}
							else
							{
								$pre = trim($formData[value]);
								$theRegionCats .= $pre."<br />";
							}
							
							if (array_key_exists($pre, $regionsCatsArray))
							{
								$allCatsArray[] = $regionsCatsArray[$pre];
							}
						}
						if ($formData[field_number] >= 6 && $formData[field_number] < 7)
						{
							$length = strlen($formData[value]);
							$findEnd = strpos($formData[value], ":");
							if ($findEnd != FALSE)
							{
								$endPos = $length - $findEnd;
								$pre = substr($formData[value], 0, -$endPos);
								$thePopulationCats .= $pre."<br />";
							}
							else
							{
								$pre = trim($formData[value]);
								$thePopulationCats .= $pre."<br />";
							}
							
							if (array_key_exists($pre, $populationCatsArray))
							{
								$allCatsArray[] = $populationCatsArray[$pre];
							}
						}
					} // end $allFormData foreach
				}
				else
				{
					// nothing returned from wp_rg_lead_detail for form
					$printThis .= "No data for form entry ".$formEntryId."<br />";
				} // end test for form data return
				
				// check to see if there are long versions of the info entered for 'description'
				$descriptionData = $wpdb->get_results("SELECT * FROM wp_rg_lead_detail_long WHERE lead_detail_id = $theDescriptionId", ARRAY_A);
				
				if (!empty($descriptionData))
				{
					foreach ($descriptionData AS $theDescriptionData)
					{
						$theDescription = $theDescriptionData[value];
					}
				}
				
				$insertContent .=	"<h3 class=\"yellow\">title:</h3>
								".$theTitle."<br />
								<br />
								<br />
								<h3 class=\"yellow\">listsrv description:</h3>";
				
				if (!empty($theDescription)) $insertContent .=	$theDescription."<br /><br />";
				
				$insertContent .=	"<h3 class=\"yellow\">how to join:</h3>";
				
				if (!empty($theHowToJoin)) $insertContent .= $theHowToJoin."<br /><br />";
				
				$insertContent .= "	<table style=\"border-collapse: collapse; width: 100%;\">
									<tr>
										<td style=\"width: 50%;\">
											<h3 class=\"yellow\">arts categories:</h3>";
				
				$insertContent .= (!empty($theArtCats)) ? $theArtCats : "&nbsp;";
				
				$insertContent .= "			</td>
										<td style=\"width: 50%;\">
											<h3 class=\"yellow\">region(s) served:</h3>";
				
				$insertContent .= (!empty($theRegionCats)) ? $theRegionCats : "&nbsp;";
				
				$insertContent .= "			</td>
									</tr>
									<tr>
										<td style=\"width: 50%;\">
											<h3 class=\"yellow\">population(s) served:</h3>";
				
				$insertContent .= (!empty($thePopulationCats)) ? $thePopulationCats : "&nbsp;";
				
				$insertContent .= "			</td>
										<td>
											&nbsp;
										</td>
									</tr>
								</table>";
				
				$insertTitle = $theTitle;
				$insertCats = $allCatsArray;
				$insertExcerpt = $theDescription;
			break;
			
			case 10: // individual practitioners
				// reset variables
				$insertTitle = "";
				$insertContent = "";
				unset($insertCats);
				$insertCats = array();
				unset($allCatsArray);
				$allCatsArray = array();
				$allCatsArray[] = 172; // first thing to put in the cats array is the resource category
				
				//print "Key: ".$key." => Value: ".$value."<br />";
				$formEntryId = $lead_id;
				$printThis .= "Form Entry ID: ".$formEntryId."<br />";
				$allFormData = $wpdb->get_results("SELECT * FROM wp_rg_lead_detail WHERE lead_id = $formEntryId", ARRAY_A);
				
				if (!empty($allFormData))
				{
					foreach ($allFormData AS $formData)
					{
						if ($formData[field_number] == 1) $theTitle = $formData[value];
						if ($formData[field_number] == 2) $theNatureOfService = $formData[value];
						if ($formData[field_number] == 2) $theNatureOfServiceId = $formData[id]; // this is needed to check to see if there is a long value for description in 'wp_rg_lead_detail_long'
						if ($formData[field_number] == 4) $theDegreesCredentials = $formData[value];
						if ($formData[field_number] == 4) $theDegreesCredentialsId = $formData[id]; // this is needed to check to see if there is a long value for description in 'wp_rg_lead_detail_long'
						if ($formData[field_number] == 5) $theBio = $formData[value];
						if ($formData[field_number] == 5) $theBioId = $formData[id]; // this is needed to check to see if there is a long value for description in 'wp_rg_lead_detail_long'
						if ($formData[field_number] == 19) $theAddress1 = $formData[value];
						if ($formData[field_number] == 20) $theAddress2 = $formData[value];
						if ($formData[field_number] == 21) $theCity = $formData[value];
						if ($formData[field_number] == 22) $theState = $formData[value];
						if ($formData[field_number] == 23) $theZipCode = $formData[value];
						if ($formData[field_number] == 7) $thePhone = $formData[value];
						if ($formData[field_number] == 8) $theEmail = $formData[value];
						if ($formData[field_number] == 9) $theWebsite = $formData[value];
						
						if ($formData[field_number] >= 10 && $formData[field_number] < 11)
						{
							$length = strlen($formData[value]);
							$findEnd = strpos($formData[value], ":");
							if ($findEnd != FALSE)
							{
								$endPos = $length - $findEnd;
								$pre = substr($formData[value], 0, -$endPos);
								$theArtCats .= $pre."<br />";
							}
							else
							{
								$pre = trim($formData[value]);
								$theArtCats .= $pre."<br />";
							}
							
							if (array_key_exists($pre, $artsCatsArray))
							{
								$allCatsArray[] = $artsCatsArray[$pre];
							}
						}
						if ($formData[field_number] >= 12 && $formData[field_number] < 13)
						{
							$length = strlen($formData[value]);
							$findEnd = strpos($formData[value], ":");
							if ($findEnd != FALSE)
							{
								$endPos = $length - $findEnd;
								$pre = substr($formData[value], 0, -$endPos);
								$theRegionCats .= $pre."<br />";
							}
							else
							{
								$pre = trim($formData[value]);
								$theRegionCats .= $pre."<br />";
							}
							
							if (array_key_exists($pre, $regionsCatsArray))
							{
								$allCatsArray[] = $regionsCatsArray[$pre];
							}
						}
						if ($formData[field_number] >= 11 && $formData[field_number] < 12)
						{
							$length = strlen($formData[value]);
							$findEnd = strpos($formData[value], ":");
							if ($findEnd != FALSE)
							{
								$endPos = $length - $findEnd;
								$pre = substr($formData[value], 0, -$endPos);
								$thePopulationCats .= $pre."<br />";
							}
							else
							{
								$pre = trim($formData[value]);
								$thePopulationCats .= $pre."<br />";
							}
							
							if (array_key_exists($pre, $populationCatsArray))
							{
								$allCatsArray[] = $populationCatsArray[$pre];
							}
						}
					} // end $allFormData foreach
				}
				else
				{
					// no data returned for form
					$printThis .= "No data for form entry ".$formEntryId."<br />";
				} // end if/else test for wp_rg_lead_detail return
				
				// check to see if there are long versions of the info entered for 'description' and 'degree(s)/credential(s)' and 'bio'
				$natureOfServiceData = $wpdb->get_results("SELECT * FROM wp_rg_lead_detail_long WHERE lead_detail_id = $theNatureOfServiceId", ARRAY_A);
				
				if (!empty($natureOfServiceData))
				{
					foreach ($natureOfServiceData AS $theNatureOfServiceData)
					{
						$theNatureOfService = $theNatureOfServiceData[value];
					}
				}
				
				$degreesCredentialsData = $wpdb->get_results("SELECT * FROM wp_rg_lead_detail_long WHERE lead_detail_id = $theDegreesCredentialsId", ARRAY_A);
				
				if (!empty($degreesCredentialsData))
				{
					foreach ($degreesCredentialsData AS $theDegreesCredentialsData)
					{
						$theDegreesCredentials = $theDegreesCredentialsData[value];
					}
				}
				
				$bioData = $wpdb->get_results("SELECT * FROM wp_rg_lead_detail_long WHERE lead_detail_id = $theBioId", ARRAY_A);
				
				if (!empty($bioData))
				{
					foreach ($bioData AS $theBioData)
					{
						$theBio = $theBioData[value];
					}
				}
				
				$insertContent .=	"<div id=\"resources_title\">".$theTitle."</div>
								<br />
								<br />
								<h3 class=\"yellow\">nature of service:</h3>";
				
				if (!empty($theNatureOfService)) $insertContent .=	$theNatureOfService."<br /><br />";
				
				$insertContent .=	"<h3 class=\"yellow\">degree(s)/credential(s):</h3>";
				
				if (!empty($theDegreesCredentials)) $insertContent .= $theDegreesCredentials."<br /><br />";
				
				$insertContent .=	"<h3 class=\"yellow\">bio:</h3>";
				
				if (!empty($theBio)) $insertContent .= $theBio."<br /><br />";
				
				$insertContent .=	"<h3 class=\"yellow\">contact:</h3>";
				
				if (!empty($theAddress1)) $insertContent .= $theAddress1."<br />";
				if (!empty($theAddress2)) $insertContent .= $theAddress2."<br />";
				if (!empty($theCity)) $insertContent .= $theCity;
				if (!empty($theState)) $insertContent .= ", ".$theState;
				if (!empty($theZipCode)) $insertContent .= " ".$theZipCode."<br />";
				if (!empty($theCountry)) $insertContent .= $theCountry."<br />";
				if (!empty($theEmail)) $insertContent .= "<a href=\"mailto:".$theEmail."\" target=\"_blank\">".$theEmail."</a><br />";
				if (!empty($thePhone)) $insertContent .= $thePhone."<br />";
				if (!empty($theWebsite)) $insertContent .= "<a href=\"".$theWebsite."\" target=\"_blank\">".$theWebsite."</a><br />";
				
				$insertContent .= "<br />";
				
				$insertContent .= "	<table style=\"border-collapse: collapse; width: 100%;\">
									<tr>
										<td style=\"width: 50%;\">
											<h3 class=\"yellow\">arts categories:</h3>";
				
				$insertContent .= (!empty($theArtCats)) ? $theArtCats : "&nbsp;";
				
				$insertContent .= "			</td>
										<td style=\"width: 50%;\'>
											<h3 class=\"yellow\">population(s) served:</h3>";
				
				$insertContent .= (!empty($thePopulationCats)) ? $thePopulationCats : "&nbsp;";
				
				$insertContent .= "			</td>
									</tr>
									<tr>
										<td style=\"width: 50%;\">
											<h3 class=\"yellow\">region(s) served:</h3>";
				
				$insertContent .= (!empty($theRegionCats)) ? $theRegionCats : "&nbsp;";
				
				$insertContent .= "			</td>
										<td style=\"width: 50%;\">
											&nbsp;
										</td>
									</tr>
								</table>";
				
				$insertTitle = $theTitle;
				$insertCats = $allCatsArray;
				$insertExcerpt = $theNatureOfService;
			break;
		} // end switch
					
		$insertPost['ID'] = $post_id;
		$insertPost['post_title'] = $insertTitle;
		$insertPost['post_content'] = $insertContent;
		$insertPost['post_excerpt'] = $insertExcerpt;
		$insertPost['post_status'] = "publish";
		$insertPost['post_author'] = 3;
		$insertPost['post_category'] = $insertCats;
		
		// for postmeta save
		$theEditLock = time().":3";
		$metaValueArray['_gform-entry-id'] = $formEntryId;
		$metaValueArray['_gform-form-id'] = $formId;
		$metaValueArray['_edit_lock'] = $theEditLock;
		
		$result_id = wp_update_post($insertPost); // on failure, $result_id = 0
		
		/* this was needed for the initial input of the resource data as a post, not needed now
		if ($result_id > 0)
		{
			// successful post save
			// now save postmeta
			$metaSuccess = 0;
			$metaFail = 0;
			foreach($metaValueArray AS $key => $value)
			{
				$postmetaSave = update_post_meta($result_id, $key, $value);
				
				($postmetaSave) ? $metaSuccess++ : $metaFail++;
			} // end foreach to do postmeta saves
			
			// metaSuccess should = 10 if all metapost data was saved
			if ($metaSuccess == 10)
			{
				// now save the post id to wp_rg_lead
				$sqlStatement = "UPDATE wp_rg_lead SET post_id = ".$post_id." WHERE id = ".$formEntryId;
				$updateLead = $wpdb->query($sqlStatement);
				
				if ($updateLead > 0)
				{
					$printThis .= "<span style=\"color: #009900;\">Post and Meta Saved for: ".$formEntryId." Post ID: ".$result_id." Lead Updated.</span><br />";
				}
				else
				{
					$printThis .= "<span style=\"color: #6666cc;\">Post and Meta Saved for: ".$formEntryId." Post ID: ".$result_id."</span> <span style=\"color: #cc0000;\">Lead NOT Updated</span><br />";
				}
			}
			else
			{
				// the postmeta failed, delete the post data and report
				$deletePost = wp_delete_post($result_id, true);
				
				if($deletePost == FALSE)
				{
					// the delete didn't work
					$printThis .= "<span style=\"font-weight: bold;\">Meta not saved, delete didn't work: ".$result_id."</span><br />";
				}
				else
				{
					// post delete successful
					$printThis .= "<span style=\"color: #ff9933;\">Retry for form entry: ".$formEntryId."</span><br />";
				}
			}
		}
		else
		{
			// failed save
			$printThis .= "<span style=\"color: #cc0000;\">NOT SAVED: ".$formEntryId."</span><br />";
		}
		*/
		
		
		//return $printThis;
	} // end rt_do_resources2posts function
	
	function rt_convert_date($theDate = NULL)
	{
		$returnThis = "No date";
		
		if (is_null($theDate)) return NULL;
		
		$monthNamesArray = array(	"Jan" 		=> 1,
								"January"		=> 1,
								"Feb"		=> 2,
								"February"	=> 2,
								"Mar"		=> 3,
								"March"		=> 3,
								"Apr"		=> 4,
								"April"		=> 4,
								"May"		=> 5,
								"Jun"		=> 6,
								"June"		=> 6,
								"Jul"		=> 7,
								"July"		=> 7,
								"Aug"		=> 8,
								"August"		=> 8,
								"Sep"		=> 9,
								"Sept"		=> 9,
								"September"	=> 9,
								"Oct"		=> 10,
								"October"		=> 10,
								"Nov"		=> 11,
								"November"	=> 11,
								"Dec"		=> 12,
								"December"	=> 12);
		
		// first try to let php take care of things
		if (($timestamp = strtotime($theDate)) === FALSE)
		{
			// php couldn't figure it out, use my code
			// check to see if the abbreviated version of the month is in the string ($theDate)
			foreach ($monthNamesArray AS $compareMonth)
			{
				if (strpos($theDate, $compareMonth) !== FALSE)
				{
					// the month name exists
					$theDateArray = explode(" ", $theDate);
					
					// change month - this is knowing that the month is always first.  in the future, may have to change to accept formats like dd-mmm-yyyy
					foreach ($monthNamesArray AS $key => $value)
					{
						if ($theDateArray[0] == $key)
						{
							$newMonth = $value;
						}
					}
					
					$newMonth = str_pad($newMonth, 2, "0", STD_PAD_LEFT); // make sure two digits
					
					// remove white space and the comma from the end of the day
					$newDay = trim($theDayArray[1]); // first make sure that no white space around value
					$newDay = rtrim($newDay); // now remove the comma, if there
					$newDay = str_pad($newDay, 2, "0", STD_PAD_LEFT); // make sure two digits
					
					// remove white space from year
					$newYear = trim($theDayArray[2]);
					
					return $newMonth."/".$newDay."/".$newYear;
				}
				elseif (strpos($theDate, "-") !== FALSE) // check for dashes in the date, convert to slashes
				{
					$theDateArray = explode("-", $theDate);
					return str_pad($theDateArray[0], 2, "0", STD_PAD_LEFT)."/".str_pad($theDateArray[1], 2, "0", STD_PAD_LEFT)."/".$theDateArray[2];
				}
			} // end compare month loop
		}
		else
		{
			return date("m/d/Y", $timestamp);
		}
		
		return; // $returnThis; -> don't need to return anything here
		
	} // end rt_convert_date function
?>
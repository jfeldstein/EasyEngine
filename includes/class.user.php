<?php
class User
{
	
	var $id;
	var $first_name;
	var $last_name;
	var $full_name;
	var $email;
	var $passhash;
	var $token;
	var $token_time;
	var $tools = array();
	var $type;
	
	function User($id)
	{
		global $db;	
		
		$sql = "SELECT * FROM `users` WHERE `id`='$id'";
		$result = $db->Run($sql);
		
		if($db->NumRows($result) == 0)
			return false;
		
		$tmp = $db->FetchRow($result);
		
		$this->id 			= $tmp['id'];
		$this->first_name	= $tmp['first_name'];
		$this->last_name	= $tmp['last_name'];
		$this->full_name	= ucwords(strtolower($this->first_name.' '.$this->last_name));
		$this->email		= $tmp['email'];
		$this->passhash		= $tmp['passhash'];
		$this->token		= $tmp['token'];
		$this->token_time	= $tmp['token_time'];;
		
		// Leftover code from iurs, tools and credits may be useful later
		/*
		$sql = "SELECT * FROM tools WHERE user_id='".$this->id."'";
		$result = $db->Run($sql);
		foreach($db->FetchRowSet($result) as $tool){
			$this->tools[$tool['tool']] = $tool['tool'];
		}
		
		$sql = "SELECT credits FROM photo_credits "
				."WHERE user_id='".$this->id."' "
				  ."AND year='".date('Y')."' "
				  ."AND month='".date('n')."' ";
		$db->Run($sql);
		if($db->NumRows()>0)
		{
			$row = $db->FetchRow();
			$this->num_photo_credits = $row['credits'];
		}
		 else
		{	$this->num_photo_credits = 0; }
		
		// Can we do this in one query?
		$sql = "SELECT * FROM `photos` as op "
			  ."WHERE EXISTS ("
				."SELECT * FROM `photos` "
				."WHERE photos.for_type=op.for_type "
				  ."AND photos.for_id=op.for_id "
				  ."AND photos.id!=op.id "
				  ."AND photos.visible='1') "
				."AND op.visible='1' "
			  ."ORDER BY op.for_type, op.for_id ";
		$tmp = $db->FetchRowSet($db->Run($sql));
		foreach($tmp as $photo)
		{
			if($usedPhotos[$tmp['for_type'].$tmp['for_id']]>=2)
				$countedPhotos[] = $photo;
			$usedPhotos[$tmp['for_type'].$tmp['for_id']]++;
		}
		$this->num_photo_credits_used = count($countedPhotos);
		
		
		$sql = "SELECT * FROM `starred_spaces` "
			  ."WHERE `user_id`='".$this->id."'";
		$tmp = $db->FetchRowSet($db->Run($sql));
		$this->starred_spaces = array();
		if(count($tmp))
		{	foreach($tmp as $save)
			{
				$this->starred_spaces[] = $save['space_id'];
		}	}
		
		*/
		
		
		return true;
	}
	
	function makeNewToken()
	{
		global $db;
		
		$this->token 	= md5($this->email.$this->full_name.time());
		$this->tokenTime= time();
		
		$sql = "UPDATE `users` "
			."SET `token`='".$this->token."', "
				."`token_time`='".$this->tokenTime."' "
			."WHERE `id`='".$this->id."'";
		$db->Run($sql);
		
		return $this->token;
	}
	
	function save_from_form($FORM)
	{
		global $db;	
		
		$this->first_name = $FORM['first_name'];
		$this->last_name = $FORM['last_name'];
		$this->email = ($FORM['email']) ? $FORM['email'] : $this->email;
		
		$this->phone = (substr($this->phone,0,1)=='1') ? substr($this->phone,1) : $this->phone;
		
		$sql = "UPDATE `users` SET "
				." `first_name`='".$this->first_name."', "
				." `last_name`='".$this->last_name."', "
				." `email`='".$this->email."' "
			." WHERE `id`='".$this->id."'";
		$db->Run($sql);
		
		return true;
	}

	// Could be useful for subscription level
	/*
	function set_type($type)
	{	
		global $db, $CONFIG;
		
		if(!in_array($type, $CONFIG['allowed_account_types']))
			return false; // Should throw exception
		
		$sql = "UPDATE `users` SET `type`='$type' WHERE `id`='".$this->id."'";
		$db->Run($sql);
	}
	*/
	
	function setPassword($pass)
	{// Not secured here
		global $db;
		
		$sql = "UPDATE `users` "
			."SET `passhash`='".md5($pass)."', "
				."`token`='', `token_time`='' "
			."WHERE `id`='".$this->id."'";
		$db->Run($sql);
	}
	
	function to_array()
	{// Update based on user data
		$array['id'] = $this->id;
		$array['first_name'] = $this->first_name;
		$array['last_name'] = $this->last_name;
		$array['full_name'] = $this->full_name;
		$array['email'] = $this->email;
	/*	$array['phone'] = $this->phone;
		$array['company'] = $this->company;
		$array['top_name'] = $this->top_name;
		$array['locations'] = $this->locations;
		$array['num_photo_credits'] = $this->num_photo_credits;
		$array['num_photo_credits_used'] = $this->num_photo_credits_used;
		$array['complete_contact'] = $this->complete_contact;
		$array['starred_spaces'] = $this->starred_spaces;
		$array['type'] = $this->type;
		$array['tools'] = $this->tools;
	*/	
		return $array;
	}
}

?>
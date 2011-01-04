<?php

// ***************************************
// **	SQL Class for EasyRefs.com		**
// **	By: Jordan Feldstein			**
// **	Started: September 24th, 2005	**
// **									**	
// **	All Rights Reserved				**
// ***************************************

// *******************************************
// **	This class simplifies interaction	**
// **	between PHP and MySQL 				**
// *******************************************


class SQL 
{

	var $connection_id;
	var $connected = false;
	var $row = array();
	var $total_queries = 0;
	var $queries = array();
	var $echoErrors = false;

	// The Constructor: Connect to Server and DB
	function SQL($host, $sqluser, $sqlpass, $dbname, $persistant = false)
	{
		$this->host	= $host;
		$this->user	= $sqluser;
		$this->password	= $sqlpass;
		$this->dbname	= $dbname;
		$this->persist	= $persistant;
		
		// Connect to server. Persistant?
		if ($persistant)
		{
			$this->connection_id = @mysql_pconnect( $this->host , $this->user , $this->password );
		}
		 else
		{
			$this->connection_id = mysql_connect( $this->host , $this->user , $this->password );
		}
		
		// Did connection succeed?
		if ($this->connection_id)
		{
			// Select Database
			$selectdb = mysql_select_db( $this->dbname , $this->connection_id );
			
			// Success?
			if (!$selectdb)
			{
				@mysql_close( $this->connection_id );
				$this->connection_id = false;
			}
			
			// Return connection ID or failure
			$this->conencted = true;
			return $this->connection_id;
		} 
		 else 
		{
			// Couldn't connect, return failure
			return false;
		}

	} // END of SQL

	function Run( $query )
	{
		// Remove last result
		unset($this->result_id);
		
		if ($query != '')
		{
			$this->total_queries++;
			$this->queries[] = $query;
			$this->result_id = @mysql_query( $query, $this->connection_id );
		}
		if ($this->result_id)
		{
			unset($this->Errorstr);
			return $this->result_id;
		}
		 else
		{
			$this->Errorstr = @mysql_error($this->connection_id);
			if($this->echoErrors)
			{
				echo "'" . $query . "': " . $this->Errorstr;
			}
			return false;
		}
	} // END of Run
	
	function RowID( $link_id = false )
	{
		if (!$link_id)
		{
			$link_id = $this->connection_id;
		}
		if ($link_id)
		{
			return @mysql_insert_id($link_id);
		}
		 else
		{
			return false;
		}
	} // End of RowID
	
	function FetchRow( $result_id = false )
	{
		if (!$result_id)
		{
			$result_id = $this->result_id;
		}
		if ($result_id)
		{
			$this->row[$result_id] = @mysql_fetch_assoc($result_id);
			return $this->row[$result_id];
		}
		 else
		{
			return false;
		}
		 
	} // END of FetchRow
	
	function FetchRowSet( $result_id = false)
	{
		if (!$result_id)
		{
			$result_id = $this->result_id;
		}
		if ($result_id) 
		{
			$rowset = array();
			
			while( $row = $this->FetchRow($result_id) )
			{
				$rowset[] = $row;
			}
			return $rowset;
		}
		 else
		{
			return false;
		}
	}
	
	function NumRows($result_id = false)
	{
		if (!$result_id)
		{
			$result_id = $this->result_id;
		}
		if ($result_id)
		{
			return @mysql_num_rows($result_id);
		}
		 else
		{
			return false;
		}
	}// END of NumRows
	
	function Error($result_id = false)
	{
		if (isset($this->Errorstr)) {
			return $this->Errorstr;
		}
		 else
		{
			return false;
		}
	} //END of Error
	
	function ShowErrors()
	{
		$this->echoErrors = true;
		
		return true;
	}
	
	function RowsAffected($result_id = false)
	{
		if (!$result_id)
		{
			$result_id = $this->result_id;
		}
		if ($result_id)
		{
			return @mysql_rows_affected($result_id);
		}
		 else
		{
			return false;
		}
	}// END of NumRows
	
	function sql_close()
	{
		if ($this->connection_id) 
		{
			return @mysql_close($this->connection_id);
		}
		 else 
		{
			return false;
		}
	} // END of sql_close
	
	
}

?>
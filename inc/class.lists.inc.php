<?php

/**
 * Handles list interactions within the app
 * 
 * PHP version 5
 * 
 * @author Jason Lengstorf
 * @author Chris Coyier
 * @copyright 2009 Chris Coyier and Jason Lengstorf
 * @license   http://www.opensource.org/licenses/mit-license.html  MIT License
 *
 */
class ColoredListsItems
{
	/**
	 * The database object
	 * 
	 * @var object
	 */
	private $_db;

	/**
	 * Checks for a database object and creates one if none is found
	 * 
	 * @param object $db
	 * @return void
	 */
	public function __construct($db=NULL)
	{
		if(is_object($db))
		{
			$this->_db = $db;
		}
		else
		{
			$dsn = "mysql:host=".DB_HOST.";dbname=".DB_NAME;
			$this->_db = new PDO($dsn, DB_USER, DB_PASS);
		}
	}

	/**
	 * Loads all list items associated with a user ID
	 * 
	 * This function both outputs <li> tags with list items and returns an
	 * array with the list ID, list URL, and the order number for a new item.
	 * 
	 * @return array	an array containing list ID, list URL, and next order
	 */
	public function loadListItemsByUser()
	{
		$sql = "SELECT
					list_items.ListID, ListText, ListItemID, ListItemColor, 
					ListItemDone, ListURL
				FROM list_items
				LEFT JOIN lists
				USING (ListID)
				WHERE list_items.ListID=(
					SELECT lists.ListID
					FROM lists
					WHERE lists.UserID=(
						SELECT users.UserID
						FROM users
						WHERE users.Username=:user
					)
				)
				ORDER BY ListItemPosition";
		if($stmt = $this->_db->prepare($sql))
		{
			$stmt->bindParam(':user', $_SESSION['Username'], PDO::PARAM_STR);
			$stmt->execute();
			$order = 0;
			while($row = $stmt->fetch())
			{
				$LID = $row['ListID'];
				$URL = $row['ListURL'];
				echo $this->formatListItems($row, ++$order);
			}
			$stmt->closeCursor();

			// If there aren't any list items saved, no list ID is returned
			if(!isset($LID))
			{
				$sql = "SELECT ListID, ListURL
						FROM lists
						WHERE UserID = (
							SELECT UserID
							FROM users
							WHERE Username=:user
						)";
				if($stmt = $this->_db->prepare($sql))
				{
					$stmt->bindParam(':user', $_SESSION['Username'], PDO::PARAM_STR);
					$stmt->execute();
					$row = $stmt->fetch();
					$LID = $row['ListID'];
					$URL = $row['ListURL'];
					$stmt->closeCursor();
				}
			}
		}
		else
		{
			echo "\t\t\t\t<li> Something went wrong. ", $db->errorInfo, "</li>\n";
		}

		return array($LID, $URL, $order);
	}

	/**
	 * Outputs all list items corresponding to a particular list ID
	 * 
	 * @return void
	 */
	public function loadListItemsByListId()
	{
		$sql = "SELECT ListText, ListItemID, ListItemColor, ListItemDone
				FROM list_items
				WHERE ListID=(
					SELECT ListID
					FROM lists
					WHERE ListURL=:list
				) 
				ORDER BY ListItemPosition";
		if($stmt = $this->_db->prepare($sql)) {
			$stmt->bindParam(':list', $_GET['list'], PDO::PARAM_STR);
			$stmt->execute();
			$order = 1;
			while($row = $stmt->fetch())
			{
				echo $this->formatListItems($row, $order);
				++$order;
			}
			$stmt->closeCursor();
		} else {
			echo "<li> Something went wrong. ", $db->error, "</li>";
		}
	}

	/**
	 * Generates HTML markup for each list item
	 * 
	 * @param array $row	an array of the current item's attributes
	 * @param int $order	the position of the current list item
	 * @return string		the formatted HTML string
	 */
	private function formatListItems($row, $order)
	{
		$c = $this->getColorClass($row['ListItemColor']);
		if($row['ListItemDone']==1)
		{
			$d = '<img class="crossout" src="images/crossout.png" '
				. 'style="width: 100%; display: block;"/>';
		}
		else
		{
			$d = NULL;
		}

		// If not logged in, manually append the <span> tag to each item
		if(!isset($_SESSION['LoggedIn'])||$_SESSION['LoggedIn']!=1)
		{
			$ss = "<span>";
			$se = "</span>";
		}
		else
		{
			$ss = NULL;
			$se = NULL;
		}

		return "\t\t\t\t<li id=\"$row[ListItemID]\" rel=\"$order\" "
			. "class=\"$c\" color=\"$row[ListItemColor]\">$ss" 
			. $row['ListText'].$d 
			. "$se</li>\n";
	}

	/**
	 * Returns the CSS class that determines color for the list item
	 * 
	 * @param int $color	the color code of an item
	 * @return string		the corresponding CSS class for the color code
	 */
	private function getColorClass($color)
	{
		switch($color)
		{
			case 1:
				return 'colorBlue';
			case 2:
				return 'colorYellow';
			case 3:
				return 'colorRed';
			default:
				return 'colorGreen';
		}
	}

	/**
	 * Adds a list item to the database
	 * 
	 * @return mixed	ID of the new item on success, error message on failure
	 */
	public function addListItem()
	{
		$list = $_POST['list'];
		$text = strip_tags(urldecode(trim($_POST['text'])), WHITELIST);
		$pos = $_POST['pos'];

		$sql = "INSERT INTO list_items
					(ListID, ListText, ListItemPosition, ListItemColor) 
    			VALUES (:list, :text, :pos, 1)";
		try
		{
			$stmt = $this->_db->prepare($sql);
			$stmt->bindParam(':list', $list, PDO::PARAM_INT);
			$stmt->bindParam(':text', $text, PDO::PARAM_STR);
			$stmt->bindParam(':pos', $pos, PDO::PARAM_INT);
			$stmt->execute();
			$stmt->closeCursor();

			return $this->_db->lastInsertId();
		}
		catch(PDOException $e)
		{
			return $e->getMessage();
		}
	}

	/**
	 * Updates the text for a list item
	 * 
	 * @return string	Sanitized saved text on success, error message on fail
	 */
	public function updateListItem()
	{
		$listItemID = $_POST["listItemID"];
		$newValue = $this->cleanInput(strip_tags(urldecode(trim($_POST["value"])), WHITELIST));
	
		$sql = "UPDATE list_items
				SET ListText=:text
				WHERE ListItemID=:id
				LIMIT 1";
		if($stmt = $this->_db->prepare($sql)) {
			$stmt->bindParam(':text', $newValue, PDO::PARAM_STR);
			$stmt->bindParam(':id', $listItemID, PDO::PARAM_INT);
			$stmt->execute();
			$stmt->closeCursor();
	
			echo $newValue;
		} else {
			echo "Error saving, sorry about that!";	
		}
	}

	/**
	 * Removes javascript from the href attribute of a submitted link
	 * 
	 * @param string $input		The string to be cleansed
	 * @return string			The clean string
	 */
	private function cleanInput($data)
	{
		// http://svn.bitflux.ch/repos/public/popoon/trunk/classes/externalinput.php
		// +----------------------------------------------------------------------+
		// | Copyright (c) 2001-2006 Bitflux GmbH                                 |
		// +----------------------------------------------------------------------+
		// | Licensed under the Apache License, Version 2.0 (the "License");      |
		// | you may not use this file except in compliance with the License.     |
		// | You may obtain a copy of the License at                              |
		// | http://www.apache.org/licenses/LICENSE-2.0                           |
		// | Unless required by applicable law or agreed to in writing, software  |
		// | distributed under the License is distributed on an "AS IS" BASIS,    |
		// | WITHOUT WARRANTIES OR CONDITIONS OF ANY KIND, either express or      |
		// | implied. See the License for the specific language governing         |
		// | permissions and limitations under the License.                       |
		// +----------------------------------------------------------------------+
		// | Author: Christian Stocker <chregu@bitflux.ch>                        |
		// +----------------------------------------------------------------------+
		//
		// Kohana Modifications:
		// * Changed double quotes to single quotes, changed indenting and spacing
		// * Removed magic_quotes stuff
		// * Increased regex readability:
		//   * Used delimeters that aren't found in the pattern
		//   * Removed all unneeded escapes
		//   * Deleted U modifiers and swapped greediness where needed
		// * Increased regex speed:
		//   * Made capturing parentheses non-capturing where possible
		//   * Removed parentheses where possible
		//   * Split up alternation alternatives
		//   * Made some quantifiers possessive

		// Fix &entity\n;
		$data = str_replace(array('&amp;','&lt;','&gt;'), array('&amp;amp;','&amp;lt;','&amp;gt;'), $data);
		$data = preg_replace('/(&#*\w+)[\x00-\x20]+;/u', '$1;', $data);
		$data = preg_replace('/(&#x*[0-9A-F]+);*/iu', '$1;', $data);
		$data = html_entity_decode($data, ENT_COMPAT, 'UTF-8');

		// Remove any attribute starting with "on" or xmlns
		$data = preg_replace('#(<[^>]+?[\x00-\x20"\'])(?:on|xmlns)[^>]*+>#iu', '$1>', $data);

		// Remove javascript: and vbscript: protocols
		$data = preg_replace('#([a-z]*)[\x00-\x20]*=[\x00-\x20]*([`\'"]*)[\x00-\x20]*j[\x00-\x20]*a[\x00-\x20]*v[\x00-\x20]*a[\x00-\x20]*s[\x00-\x20]*c[\x00-\x20]*r[\x00-\x20]*i[\x00-\x20]*p[\x00-\x20]*t[\x00-\x20]*:#iu', '$1=$2nojavascript...', $data);
		$data = preg_replace('#([a-z]*)[\x00-\x20]*=([\'"]*)[\x00-\x20]*v[\x00-\x20]*b[\x00-\x20]*s[\x00-\x20]*c[\x00-\x20]*r[\x00-\x20]*i[\x00-\x20]*p[\x00-\x20]*t[\x00-\x20]*:#iu', '$1=$2novbscript...', $data);
		$data = preg_replace('#([a-z]*)[\x00-\x20]*=([\'"]*)[\x00-\x20]*-moz-binding[\x00-\x20]*:#u', '$1=$2nomozbinding...', $data);

		// Only works in IE: <span style="width: expression(alert('Ping!'));"></span>
		$data = preg_replace('#(<[^>]+?)style[\x00-\x20]*=[\x00-\x20]*[`\'"]*.*?expression[\x00-\x20]*\([^>]*+>#i', '$1>', $data);
		$data = preg_replace('#(<[^>]+?)style[\x00-\x20]*=[\x00-\x20]*[`\'"]*.*?behaviour[\x00-\x20]*\([^>]*+>#i', '$1>', $data);
		$data = preg_replace('#(<[^>]+?)style[\x00-\x20]*=[\x00-\x20]*[`\'"]*.*?s[\x00-\x20]*c[\x00-\x20]*r[\x00-\x20]*i[\x00-\x20]*p[\x00-\x20]*t[\x00-\x20]*:*[^>]*+>#iu', '$1>', $data);

		// Remove namespaced elements (we do not need them)
		$data = preg_replace('#</*\w+:\w[^>]*+>#i', '', $data);

		do
		{
			// Remove really unwanted tags
			$old_data = $data;
			$data = preg_replace('#</*(?:applet|b(?:ase|gsound|link)|embed|frame(?:set)?|i(?:frame|layer)|l(?:ayer|ink)|meta|object|s(?:cript|tyle)|title|xml)[^>]*+>#i', '', $data);
		}
		while ($old_data !== $data);

		return $data;
	}

	/**
	 * Removes a list item from the database
	 * 
	 * @return string	message indicating success or failure
	 */
	public function deleteListItem()
	{
		$list = $_POST['list'];
		$item = $_POST['id'];

		$sql = "DELETE FROM list_items
				WHERE ListItemID=:item
				AND ListID=:list
				LIMIT 1";
		try
		{
			$stmt = $this->_db->prepare($sql);
			$stmt->bindParam(':item', $item, PDO::PARAM_INT);
			$stmt->bindParam(':list', $list, PDO::PARAM_INT);
			$stmt->execute();
			$stmt->closeCursor();

			$sql = "UPDATE list_items
					SET ListItemPosition=ListItemPosition-1
					WHERE ListID=:list
					AND ListItemPosition>:pos";
			try
			{
				$stmt = $this->_db->prepare($sql);
				$stmt->bindParam(':list', $list, PDO::PARAM_INT);
				$stmt->bindParam(':pos', $_POST['pos'], PDO::PARAM_INT);
				$stmt->execute();
				$stmt->closeCursor();
				return "Success!";
			}
			catch(PDOException $e)
			{
				return $e->getMessage();
			}
		}
		catch(Exception $e)
		{
			return $e->getMessage();
		}
	}

	/**
	 * Changes the order of a list's items
	 * 
	 * @return string	a message indicating the number of affected items
	 */
	public function changeListItemPosition()
	{
		$listid = (int) $_POST['currentListID'];
		$startPos = (int) $_POST['startPos'];
		$currentPos = (int) $_POST['currentPos'];
		$direction = $_POST['direction'];

		if($direction == 'up')
		{
			/*
			 * This query modifies all items with a position between the item's
			 * original position and the position it was moved to. If the 
			 * change makes the item's position greater than the item's 
			 * starting position, then the query sets its position to the new
			 * position. Otherwise, the position is simply incremented.
			 */ 
			$sql = "UPDATE list_items
					SET ListItemPosition=(
						CASE 
							WHEN ListItemPosition+1>$startPos THEN $currentPos
							ELSE ListItemPosition+1 
						END) 
					WHERE ListID=$listid 
					AND ListItemPosition BETWEEN $currentPos AND $startPos";
		}
		else
		{
			/*
			 * Same as above, except item positions are decremented, and if the
			 * item's changed position is less than the starting position, its
			 * position is set to the new position.
			 */
			$sql = "UPDATE list_items
					SET ListItemPosition=(
						CASE 
							WHEN ListItemPosition-1<$startPos THEN $currentPos
							ELSE ListItemPosition-1 
						END) 
					WHERE ListID=$listid 
					AND ListItemPosition BETWEEN $startPos AND $currentPos";
		}

		$rows = $this->_db->exec($sql);
		echo "Query executed successfully. ",
			"Affected rows: $rows";
	}

	/**
	 * Changes the color code of a list item
	 * 
	 * @return mixed	returns TRUE on success, error message on failure
	 */
	public function changeListItemColor()
	{
		$sql = "UPDATE list_items
				SET ListItemColor=:color
				WHERE ListItemID=:item
				LIMIT 1";
		try
		{
			$stmt = $this->_db->prepare($sql);
			$stmt->bindParam(':color', $_POST['color'], PDO::PARAM_INT);
			$stmt->bindParam(':item', $_POST['id'], PDO::PARAM_INT);
			$stmt->execute();
			$stmt->closeCursor();
			return TRUE;
		} catch(PDOException $e) {
			return $e->getMessage();	
		}
	}

	/**
	 * Changes the ListItemDone state of an item
	 * 
	 * @return mixed	returns TRUE on success, error message on failure
	 */
	public function toggleListItemDone()
	{
		$sql = "UPDATE list_items
				SET ListItemDone=:done
				WHERE ListItemID=:item
				LIMIT 1";
		try
		{
			$stmt = $this->_db->prepare($sql);
			$stmt->bindParam(':done', $_POST['done'], PDO::PARAM_INT);
			$stmt->bindParam(':item', $_POST['id'], PDO::PARAM_INT);
			$stmt->execute();
			$stmt->closeCursor();
			return TRUE;
		} catch(PDOException $e) {
			return $e->getMessage();	
		}
	}
}

?>
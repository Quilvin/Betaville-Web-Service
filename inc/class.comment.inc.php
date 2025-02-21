<?php
/**  
 *  Betaville Web Service - A service for accessing data from a Betaville server via HTTP requests
 *  Copyright (C) 2011 Skye Book <skye.book@gmail.com>
 *  
 *  This program is free software: you can redistribute it and/or modify
 *  it under the terms of the GNU General Public License as published by
 *  the Free Software Foundation, either version 3 of the License, or
 *  (at your option) any later version.
 *  
 *  This program is distributed in the hope that it will be useful,
 *  but WITHOUT ANY WARRANTY; without even the implied warranty of
 *  MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 *  GNU General Public License for more details.
 *  
 *  You should have received a copy of the GNU General Public License
 *  along with this program.  If not, see <http://www.gnu.org/licenses/>.
 */

/**
 * Actions for interacting with comments
 * @author Skye Book
 */
class CommentActions{
	
	private $_db;

	public function __construct($db=null){
		include_once "config.php";
		include_once "class_names.php";
		include_once "db_constants.php";
		
		if(is_object($db)){
			$this->_db=$db;
		}
		else{
			$dsn = "mysql:host=".DB_HOST.";dbname=".DB_NAME;
			$this->_db = new PDO($dsn, DB_USER, DB_PASS);
		}
	}

	public function getCommentsForDesign($id){
		$sql = 'SELECT * FROM '.COMMENT_TABLE.' WHERE '.COMMENT_SPAMVERIFIED.' = 0 AND '.COMMENT_DESIGN.'=:designID';
		try{
			$stmt = $this->_db->prepare($sql);
			$stmt->bindParam(":designID", $id, PDO::PARAM_INT);
			$stmt->execute();
			$comments = array();
			while($row=$stmt->fetch()){
				// only include this result if it hasn't been verified as spam
				if($row[COMMENT_SPAMVERIFIED]==0){
					$comments[] = $this->commentFromRow($row);
				}
			}
			return $comments;
		}catch(PDOException $e){
			echo'exception';
			return false;
		}
		return null;
	}
	
	public function getRecentComments($numberOfComments){
		// "SELECT * FROM " + DBConst.COMMENT_TABLE + " WHERE "+DBConst.COMMENT_SPAMVERIFIED+" = 0 ORDER BY "+DBConst.COMMENT_ID +" DESC LIMIT ?"
		$sql = 'SELECT * FROM '.COMMENT_TABLE.' WHERE '.COMMENT_SPAMVERIFIED.' = 0 ORDER BY '.COMMENT_ID.' DESC LIMIT :numberOfComments';
		try{
			$stmt = $this->_db->prepare($sql);
			$stmt->bindParam(":numberOfComments", $numberOfComments, PDO::PARAM_INT);
			$stmt->execute();
			$comments = array();
			while($row=$stmt->fetch()){
				// only include this result if it hasn't been verified as spam
				if($row[COMMENT_SPAMVERIFIED]==0){
					$comments[] = $this->commentFromRow($row);
				}
			}
			return $comments;
		}catch(PDOException $e){
			echo'exception';
			return false;
		}
		return null;
	}
	
	public function getNotificationsForUser($username){
		// select * from comment join design on comment.designid = design.designid where design.isalive=1 AND (design.favelist like '%sbook%' OR design.user like 'sbook' OR comment.user like 'sbook');
		$sql = 'SELECT * FROM '.COMMENT_TABLE.' JOIN '.DESIGN_TABLE.' ON '.COMMENT_TABLE.'.'.COMMENT_DESIGN.' = '.DESIGN_TABLE.'.'.DESIGN_ID.' WHERE '.DESIGN_TABLE.'.'.DESIGN_IS_ALIVE.'=1 AND ('.DESIGN_TABLE.'.'.DESIGN_FAVE_LIST.' LIKE :wildcardname OR '.DESIGN_TABLE.'.'.DESIGN_USER.' LIKE :username OR '.COMMENT_TABLE.'.'.COMMENT_USER.' LIKE :username)';
		$wildcardName = '%'.$username.'%';
		try{
			$stmt = $this->_db->prepare($sql);
			$stmt->bindParam(":wildcardname", $wildcardName, PDO::PARAM_STR);
			$stmt->bindParam(":username", $username, PDO::PARAM_STR);
			$stmt->execute();
			$comments = array();
			while($row=$stmt->fetch()){
				// only include this result if it hasn't been verified as spam
				if($row[COMMENT_SPAMVERIFIED]==0){
					$comments[] = $this->commentFromRow($row);
				}
			}
			return $comments;
		}catch(PDOException $e){
			echo'exception';
			return false;
		}
		return null;
	}
	
	private function commentFromRow($row){
		return array(COMMENT_ID=>$row[COMMENT_ID], COMMENT_DESIGN=>$row[COMMENT_DESIGN], COMMENT_USER=>$row[COMMENT_USER],COMMENT_TEXT=>$row[COMMENT_TEXT],
		COMMENT_DATE=>$row[COMMENT_DATE],COMMENT_REPLIESTO=>$row[COMMENT_REPLIESTO]);
	}
}
?>
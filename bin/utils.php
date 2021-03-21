<?php


defined('N') or define('N', "\n");
defined('BR') or define('BR', "<br/>\n");
defined('TAB') or define('TAB', "\t");

defined('KB_ADMIN_ID') or define('KB_ADMIN_ID', "1");


function askKB($data, $debugMe = FALSE){
    
    $curl = curl_init();
    curl_setopt($curl, CURLOPT_URL,KB_URL);
    curl_setopt($curl, CURLOPT_POST, 1);
    curl_setopt($curl, CURLOPT_USERPWD, "jsonrpc:".KB_TOKEN);
    curl_setopt($curl, CURLOPT_POSTFIELDS, json_encode($data));
    
    // Ignore SSL selfsigned certificates
    curl_setopt ( $curl, CURLOPT_SSL_VERIFYHOST, 0 );
	curl_setopt ( $curl, CURLOPT_SSL_VERIFYPEER, 0 );
	
	curl_setopt ( $curl, CURLOPT_RETURNTRANSFER, 1 );
	

 	$result = curl_exec ($curl);
	 if ($debugMe){
	    echo "\n ---------------------- data :\n";
	    var_dump($data);
	    echo "\n ---------------------- curl_error :\n";
		echo curl_error($curl);
	 }
    
    curl_close ($curl);
	
	
	$pretty = json_decode($result);
	if ($debugMe){
	  echo "\n ---------------------- result :\n";
      var_dump($pretty);
	  echo "\n ----------------------\n";
	}
	return $pretty;
}

function getAllProjects(){
	//echo "getAllProjects ... ".N;
	$data1 = array(
		'jsonrpc'=>'2.0',
		'method'=>'getAllProjects',
		'id'=>1
	);
	$projectsArray = askKB($data1);
    //print_r($projectsArray) ;
	return $projectsArray ;
}

function getAllSLines($projectId){
	//echo "getAllSLines ... ".N;
	$data1 = array(
		'jsonrpc'=>'2.0',
		'method'=>'getAllSwimlanes',
		'id'=>1,
		'params'=>array($projectId)
	);
	$SLineArray = askKB($data1);
	//print_r($SLineArray) ;
	return $SLineArray;
}

function getAllTasks($projectId){
    //echo "getAllTasks ... ".N;
    $data1 = array(
        'jsonrpc'=>'2.0',
        'method'=>'getAllTasks',
        'id'=>1,
        'params'=>array($projectId)
    );
    $results = askKB($data1);
    //print_r($results) ;
    return $results;
}

function getAllCollumns($projectId){
    //echo "getAllCollumns ... ".N;
    $data1 = array(
        'jsonrpc'=>'2.0',
        'method'=>'getColumns',
        'id'=>1,
        'params'=>array($projectId)
    );
    $results = askKB($data1);
    //print_r($results) ;
    return $results;
}

function getTabCollumnsNamesById($projectId){
	$objReturn = getAllCollumns($projectId);
	//print_r($objReturn);
	$columnById = array();
	foreach ($objReturn->result as $KBColumn){
		$columnID = $KBColumn->id;
		$columnName = $KBColumn->title;
		echo "$columnID => $columnName".N;
		$columnById[$columnID]=$columnName;
	}
	return $columnById;

}


function getOneTask($TKId){
    //echo "getOneTask ... ".N;
    $data1 = array(
        'jsonrpc'=>'2.0',
        'method'=>'getTask',
        'id'=>1,
        'params'=>array('task_id'=>$TKId)
    );
    $results = askKB($data1);
    //print_r($results) ;
    return $results;
}

function createTask($project_id, $id_SL, $column_id, $tsk_title){
	echo "[$project_id], [$id_SL], [$column_id], [$tsk_title] : createTask ... ".N;
	$params2 = array(
		'title'=> "$tsk_title",
		'project_id'=> "$project_id",
		'swimlane_id'=> "$id_SL",
		'column_id' => "$column_id"
	);
	$data2 = array(
		'jsonrpc'=>'2.0',
		'method'=>'createTask',
		'id'=>1,
		'params'=> $params2
	);


	$returnInfos = askKB($data2);
	//print_r($returnInfos) ;
	return $returnInfos->result;
}

function createIfNotExist_project($projectHandle){
	$projectsArray = getAllProjects();
	foreach($projectsArray as $projectTabVals){
		if (is_array($projectTabVals) || is_object($projectTabVals)){
			foreach($projectTabVals as $projectObject){
				//print_r($val);
				$id = $projectObject->id;
				$name = $projectObject->name;
				$identifier = $projectObject->identifier;
				if ($projectHandle == $name) {
					echo "Exist PRJ : $id => $name  ".N;
                   return $id;
				}
				
				
			}
		}
		
	}

	// if not exiting, create it !
	echo "Non trouvé : on cree le projet ".N;
	$paramsCreateProj = array(
		'name'=> "$projectHandle",
		'owner_id'=> KB_ADMIN_ID
	);
	$dataCreateProj = array(
		'jsonrpc'=>'2.0',
		'method'=>'createProject',
		'id'=>1,
		'params'=> $paramsCreateProj
	);
    $returnInfos = askKB($dataCreateProj);
    link_project2user($returnInfos->id, KB_ADMIN_ID);

	
	//print_r($returnInfos) ;
	return $returnInfos->result;

}

function link_project2user($idPrj, $idUser){

    $defaultRole = "project-manager";
    $paramsLink = array(
        $idPrj,
        $idUser,
        $defaultRole
    );
	$dataLinkProj2user = array(
        'jsonrpc'=>'2.0',
        'method'=>'addProjectUser',
        'id'=>1,
        'params'=> $paramsLink
    );


	$returnInfos = askKB($dataLinkProj2user);

	//print_r($returnInfos) ;
	return $returnInfos->result;

}

function createIfNotExist_swimLine($projectId, $SLineName){
	$SLineArray = getAllSLines($projectId);
	foreach($SLineArray as $SLineTabVals){
		if (is_array($SLineTabVals) || is_object($SLineTabVals)){
			foreach($SLineTabVals as $projectSLine){
				//print_r($val);
				$id = $projectSLine->id;
				$name = $projectSLine->name;
				
				if ($SLineName == $name) {
					echo "Exist SL : $id => $name ".N;
                   return $id;
				}
				
				
			}
		}
		
	}

	// if not exiting, SwimLine don't exist, so create it !
	echo "Non trouvé : on cree la SwimLine ".N;
	$paramsCreateSL = array(
		"$projectId",
		"$SLineName"
	);
	$dataCreateSL = array(
		'jsonrpc'=>'2.0',
		'method'=>'addSwimlane',
		'id'=>1,
		'params'=> $paramsCreateSL
	);


	$returnInfos = askKB($dataCreateSL);
	
	//print_r($returnInfos) ;
	return $returnInfos->result;

}

function addTaskFromFile($projectName, $SLName, $column_id,$tsk_title ){
	$idProject = createIfNotExist_project($projectName);
	$idSL = createIfNotExist_swimLine($idProject, $SLName);
	$idTask = createTask($idProject, $idSL, $column_id, $tsk_title);
	echo "Task [ID:$idTask] created. IDP:$idProject, IDSL:$idSL, IDC:$column_id ".N;
}




function getAllUsers(){
    //echo "getAllUsers ... ".N;
    $data1 = array(
        'jsonrpc'=>'2.0',
        'method'=>'getAllUsers',
        'id'=>1
    );
    $results = askKB($data1);
    //print_r($results) ;
    return $results;
}
function getAllLogins(){
	$objReturn = getAllUsers();
    $loginsById = array();
	foreach ($objReturn->result as $KBuser){
		$userid = $KBuser->id;
		$userLogin = $KBuser->username;
		//echo "$userid => $userLogin".N;
		$loginsById[$userid]=$userLogin;
	}
	return $loginsById;
}
function getTaskLogins($loginsById, $infoTask){
	print_r($infoTask);
	$tkownid = $infoTask->owner_id;
	if (array_key_exists($tkownid, $loginsById)) {
		$loginOwner = $loginsById[$tkownid];
	}else{
		$loginOwner = "(undefined!)";
	}
	
	return $loginOwner;
}



function getKBStructure(){
    // c'est pas optimal ... mais ca marche ...
    $objReturn = getAllProjects();
	$loginsById = getAllLogins();
	
    foreach ($objReturn->result as $KBprojet){
        $pjid = $KBprojet->id;
        $pjname = $KBprojet->name;
        echo N."PROJETS : [$pjid] $pjname".N;
        $assoc_IDTask_IDSwim_Array = getTaskBySwimLines($pjid);
        $objReturn =  getAllSLines($pjid);
        //echo TAB."SWIMLINE :".N;
        foreach ($objReturn->result as $KBswimLine){
            $slid = $KBswimLine->id;
            $slname = $KBswimLine->name;
            echo TAB."SWIMLINE : [$slid] $slname".N;
            foreach ($assoc_IDTask_IDSwim_Array as $key => $val){
                if ($val == $slid){
                    $objTask = getOneTask($key);
                    $infoTask = $objTask->result;
                    //print_r($infoTask);
                    $tkid = $infoTask->id;
					$tkname = $infoTask->title;
					$loginOwner = getTaskLogins($loginsById, $infoTask);
                    echo TAB.TAB."TASK : [$tkid] $loginOwner : $tkname".N;

                }
            }
        }
    }
}

function getTaskForUsersByColumns(){
    // c'est pas optimal ... mais ca marche ...
    $objReturn = getAllProjects();
	$loginsById = getAllLogins();
	$recapTotal = array();
    foreach ($objReturn->result as $KBprojet){
        $pjid = $KBprojet->id;
        $pjname = $KBprojet->name;
		echo N."PROJETS : [$pjid] $pjname".N;
		$colmnArray = getTabCollumnsNamesById($pjid);

        $assoc_IDTask_IDSwim_Array = getTaskBySwimLines($pjid);
        $objReturn =  getAllSLines($pjid);
        //echo TAB."SWIMLINE :".N;
        foreach ($objReturn->result as $KBswimLine){
            $slid = $KBswimLine->id;
            $slname = $KBswimLine->name;
            echo TAB."SWIMLINE : [$slid] $slname".N;
            foreach ($assoc_IDTask_IDSwim_Array as $key => $val){
                if ($val == $slid){
                    $objTask = getOneTask($key);
                    $infoTask = $objTask->result;
                    //print_r($infoTask);
                    $tkid = $infoTask->id;
					$tkname = $infoTask->title;
					$loginOwner = getTaskLogins($loginsById, $infoTask);
                    echo TAB.TAB."TASK : [$tkid] $loginOwner : $tkname".N;

                }
            }
        }
    }
}





function getTaskBySwimLines($project_id){
    // contruit un tableau idTask => idSwimline
    $allTasksObjReturn = getAllTasks($project_id);
    $returnArray=array();

    foreach ($allTasksObjReturn->result as $aTaskObject){
        $idTsK = $aTaskObject->id;
        $idSL = $aTaskObject->swimlane_id;

        if (isset($idSL)) {
            $returnArray[$idTsK]=$idSL;
        }else {
            $returnArray[$idTsK]="NOSWL";
        }
        unset($idTsK);
        unset($idSL);
    }
    return $returnArray;
}

function importFileToKBTask($fileName){
	echo "\n ################################ Lecture du CSV";
	$lines = file($fileName);

	$defaultColumn_id = "";
	
	$project_title = "";
	$swimline_title = "";
	$task_title = "";
	$subtask_title = "";
	
	foreach ($lines as $line){
		$parts=explode(";", $line);
		if (isset($parts[0])){
			if ($parts[0] !== ""){
				$project_title = trim($parts[0]);
			}
		}
		if (isset($parts[1])){
			if ($parts[1] !== ""){
				$swimline_title = trim($parts[1]);
			}
		}
	
		if (isset($parts[2])){
			if ($parts[2] !== ""){
				$task_title = trim($parts[2]);
			}
		}
		
		
		if (isset($parts[3])){
			if ($parts[3] !== ""){
				$subtask_title = trim($parts[3]);
			}
		}

        if ($project_title === ""){continue;}
        if ($swimline_title === ""){continue;}
        if ($task_title === ""){continue;}

		echo "\n ##### [project_title = $project_title ] ";
		echo "[swimline_title = $swimline_title ] ";
		echo "[task_title = $task_title ] ";

		if ($subtask_title !== "") {
			echo "[subtask_title = $subtask_title ]".N;
			$idTask = addTaskFromFile($project_title, $swimline_title, $defaultColumn_id, $task_title );
		}else {
			$idTask = addTaskFromFile($project_title, $swimline_title, $defaultColumn_id, $task_title );
		}
		
	
	}
	
}

?>
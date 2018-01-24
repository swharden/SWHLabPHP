<?php

/* 

----- ABOVE THIS IS THE STIMULATED ABF CORE ----
MOVE THINGS UP THERE ONLY WHEN THEY ARE POLISHED

*/

?>


<html>
<head>
<title>ABFview</title>  
<link rel="stylesheet" href="styles.css">
<style>
body {font-family: sans-serif;}
a {color: blue; text-decoration: none;}
a:hover {color: blue;text-decoration: underline;}
</style>
</head>
<body>

<?php

class ProjectFolder{

    /* 
    
    EXPERIMENTAL PROJECT ORGANIZATION

    Experiments are organized into projects. All experimental data for a project is stored in a single folder.
    Details of the project are stored in "summary.txt", "experiment.txt", "summary.md", or "experiment.md"
    The experiment project folder full path is called "path_root".

        path_root\experiment.txt - explains the conditions used to collect the data (animal, bath, drugs, etc.). This file is required**
        path_root\abfs\ - stores all ABFs and TIFs acquired at a rig. The website can analyze/display ABFs same as before.
        path_root\analysis\ - small amounts of analyzed data (i.e., XLS) or graphs (JPG, PNG, TIF) for reference in the future. It is not for OPJs.
        path_root\imaging\ - folder for IHC and montages
        path_root\2P\ folder with T-Series, Z-Series, line scans, etc. The website can analyze/display line scans same as before.
        path_root\misc\ folder (with any name) can be used to store anything (temporary OPJs, screenshots, PNGs of graphs, notes, etc.)

    */

    function __construct($project_path){
        $this->path_given = $project_path;
        $this->path_root = $this->path_to_local($project_path);

        // scan contents
        $this->contents = scandir($this->path_root);
        sort($this->contents);
        $this->files = [];
        $this->folders = [];
        foreach ($this->contents as $fname){
            if ($fname=='.'||$fname=='..') continue;
            if (is_file($this->path_root.'/'.$fname)) $this->files[]=$fname;
            else $this->folders[]=$fname;
        }
        $this->files_lowercase = array_change_key_case($this->files);
        $this->folders_lowercase = array_change_key_case($this->folders);

        echo $this->files_uppercase[0];

        // look for common experiment and summary files
        $expFileNames=array("experiment.txt", "experiment.md", "summary.txt", "summary.md");
        $this->summary_files=[];
        foreach ($expFileNames as $expFileName){
            if (in_array(strtolower($expFileName),$this->files_lowercase)) {
                $this->summary_files[]=$expFileName;
            }
        }
        $this->contains_summary = (count($this->summary_files)>0 ? true : false);
        
        // see if the folder has ABF files (and if so, look more at them)
        $this->files_abf=[];
        $this->abf_ids=[];
        $this->abf_parents=[];
        foreach ($this->contents as $fname){
            if (strtolower(pathinfo($fname, PATHINFO_EXTENSION))=='abf'){
                $this->files_abf[]=$fname;
                $this->abf_ids[]=basename($fname);
            }
        }
        $this->contains_abfs = (count($this->files_abf)>0 ? true : false);
        if ($this->contains_abfs){
            foreach ($this->abf_ids as $abfID){
                if (in_array($abfID,$this->abf_parents)) continue;
                foreach ($this->files as $fname){
                    if (strtolower(pathinfo($fname, PATHINFO_EXTENSION))=='abf') continue;
                    if (strpos($abfID,$fname)==0){
                        $this->abf_parents[]=$abfID;
                        break;
                    }
                }
            }
        }

        // look for a "swhlab" folder (commonly loaded with auto-generated files)
        $this->path_swhlab=$this->path_root.'\swhlab';
        if (file_exists($this->path_swhlab)){
            $this->swhlab_contents=scandir($this->path_swhlab);
            $this->contains_swhlab=true;
        } else {
            $this->swhlab_contents=[];
            $this->contains_swhlab=false;
        }

        // display debug info
        $this->display_info();
    }

    function display_info(){
        // echo debug info
        echo "<div style='font-family: monospace; background: #EEE; border: 1px solid #CCC; padding: 10px; margin: 10px;'>";
        echo "<b>PROJECT INFORMATION</b><br>";
        echo "root path provided: $this->path_given<br>";
        echo "root local path: ".$this->path_root."<br>";
        echo "root network path: ".$this->path_to_url($this->path_root)."<br>";
        echo "root URL: ".$this->path_to_url($this->path_root, true)."<br>";
        echo "root path folder count: ".count($this->folders)."<br>";
        echo "root path file count: ".count($this->files)."<br>";
        echo "path contains a summary: ".($this->contains_summary ? "true" : "false")."<br>";
        echo "path contains ./swhlab/ folder: ".($this->contains_swhlab ? "true" : "false")."<br>";
        echo "swhlab folder file count: ".count($this->swhlab_contents)."<br>";
        echo "path contains abf files: ".($this->contains_abfs ? "true" : "false")."<br>";
        echo "abf file count: ".count($this->files_abf)."<br>";
        echo "abf parent count: ".count($this->abf_parents)."<br>";
        echo "</div>";
    }

    function path_to_url($path, $linkToo=false){
        $path=$this->path_to_local($path);
        $url=str_replace("D:\X_Drive","/X",$path);
        $url=str_replace("\\","/",$url);
        if ($linkToo) $url = "<a href='$url'>$url</a>";
        return $url;
    }

    function path_to_network($path){
        $path=$this->path_to_local($path);
        $path=str_replace("D:\X_Drive","X:",$path);
        return $path;
    }

    function path_to_local($path){
        // always provide uppercase drive letter and backslash separators
        $path=str_replace("x:","X:",$path);
        $path=str_replace("X:","D:\X_Drive",$path);
        $path=str_replace("/","\\",$path); // this kills me
        return $path;
    }

}

$project = new ProjectFolder('x:\Data\OTR-Cre/PFC inj eYFP OXT response');
$project = new ProjectFolder('x:\Data\OTR-Cre/PFC inj eYFP OXT response\abfs');

?>

</body>
</html>
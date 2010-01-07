<?php
include_once dirname(__FILE__).'/TaskImportExport.php';

class MsProjectImporter implements TaskImporter
{
    public $projectService;
    
    function import($project, $filename)
    {
        $handle = fopen($filename, "r");
        $fields = fgetcsv($handle, 1000, "	");
        $detail = array();
        while($data = fgetcsv($handle, 1000, "	")) {
            $detail[] = $data;
        }

        $x = 0;
        $y = 0;

        if (!in_array('Task Name', $fields)) {
            throw new Exception("Malformed input file");
        }

        $errors = array();
        $lines = array();
        foreach($detail as $i) {
            if (count($i) != count($fields)) {
                $errors[] = 'Line not well formed';
                continue;
            }
            foreach($fields as $z) {
                $fieldName = trim($z);
                if (!mb_strlen($fieldName)) {
                    ++$y;
                    continue;
                }
                $lines[$x][$fieldName] = trim($i[$y]);
                ++$y;
            }
            $y = 0;
            $x++;
        }
        $errors = array();
        $number = 0;

        foreach ($lines as $line) {
            // Get the task if it exists
            $title = $line['Task Name'];
            if (!mb_strlen($title)) {
                continue;
            }
            $existing = $this->projectService->getTasks(array('projectid='=>$project->id, 'title=' => $line['Task Name'], 'complete=' => 0));
            if (count($existing)) {
                $task = $existing[0];
            } else {
                $task = new Task();                
            }

            $task->title = $line['Task Name'];
            
            $task->startdate = $this->getDate($line['Start']);
            $task->due = $this->getDate($line['Finish']);

            $task->projectid = $project->id;

            
            // save the updated task info
            $this->projectService->saveTask($task);
        }
    }
    
    /**
     * The dates out of ganttproject export are a bit dumb in that they don't give a
     * 4 digit year. 
     */
    private function getDate($input)
    {
        if (!preg_match('|(\d+)/(\d+)/(\d+)|', $input, $date)) {
            return '';
        }
        return '20'.$date[3].'-'.$date[2].'-'.$date[1].' 00:00:00';
    }
}
?>
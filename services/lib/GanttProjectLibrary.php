<?php
include_once dirname(__FILE__).'/TaskImportExport.php';

class GanttProjectImporter implements TaskImporter
{
    /**
     * The project service to use
     *
     * @var ProjectService
     */
    public $projectService;
    
    public function import($project, $filename)
    {
        $handle = fopen($filename, "r");
        $fields = fgetcsv($handle, 1000, ",");
        $detail = array();
        while($data = fgetcsv($handle, 1000, ",")) {
            $detail[] = $data;
        }

        $x = 0;
        $y = 0;

        if (!in_array('Name', $fields)) {
            throw new Exception("Malformed input file");
        }
        
        $errors = array();
        $lines = array();
        foreach($detail as $i) {
            if (count($i) != count($fields)) {
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
            $title = $line['Name'];
            if (!mb_strlen($title)) {
                continue;
            }
            $existing = $this->projectService->getTasks(array('projectid='=>$project->id, 'title=' => $line['Name'], 'complete=' => 0));
            if (count($existing)) {
                $task = $existing[0];
            } else {
                $task = new Task();                
            }

            $task->title = $line['Name'];
            
            $task->startdate = $this->getDate($line['Begin date']);
            $task->due = $this->getDate($line['End date']);
            $task->description = $line['Notes'];
            if (mb_strlen($line['Resources'])) {
	            $users = split(';', $line['Resources']);
	            $task->userid = $users;                
            }
            
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
        $date = split('/', $input);
        if (count($date) != 3) {
            return '';
        }
        
        return '20'.$date[2].'-'.$date[1].'-'.$date[0].' 00:00:00';
    }
}

class GanttProjectExporter implements TaskExporter
{
    private $tasks;
    private $currentRow = 0;
    
    public function setTasks($tasks)
    {
        $this->tasks = $tasks;
    }
    
    public function getHeaderRow()
    {
        return array(
            'ID',
            'Name',
            'Begin date',
            'End date',
            'Duration',
            'Completion',
            'Web Link',
            'Resources',
            'Notes',
        );
    }
    
    public function getNextDataRow()
    {
        if (!isset($this->tasks[$this->currentRow])) {
            return null;
        }

        $task = $this->tasks[$this->currentRow++];
        /* @var $task Task */

        $row = array(
            '',
            $task->title,
            $task->startdate,
            $task->due,
            '',
            $task->getPercentage(),
            '',
            '',
            $task->description,
        );
        
        return $row;
        
    }
    
    public function reset()
    {
        $this->currentRow = 0;
    }
}
?>
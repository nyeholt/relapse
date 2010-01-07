<?php

interface TaskImporter
{
    public function import($project, $filename);
}

interface TaskExporter
{
    public function setTasks($tasks);
    public function getHeaderRow();
    public function getNextDataRow();
    public function reset();
}
?>
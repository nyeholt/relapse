<html>
<head>
<style type="text/css">
body {
	font-size: 10pt;
	font-family: Verdana;
}
table { 
border-collapse: collapse;
}
</style>
</head>

<body>

<h1>Project status for "<?php $this->o($this->project->title); ?>"</h1>
<?php if (mb_strlen($this->status->completednotes)): ?>
<h2>Summary</h2>
<p>
	<?php $this->wikiCode($this->status->completednotes); ?>
</p>

<?php endif; ?>
<?php if (mb_strlen($this->status->todonotes)): ?>

<h2>Todo</h2>
<p>
	<?php $this->wikiCode($this->status->todonotes); ?>
</p>
<?php endif; ?>

    <?php 
    $view = new CompositeView();
    $view->project = $this->project;
    $view->status = $this->status;
    $content = $view->render('project/displaystatus.php');
    echo $content;
    ?>

</body>
</html>
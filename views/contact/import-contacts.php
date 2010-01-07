<p>
File must be a CSV in the format. Only First Name, Last Name and Primary Email
are required, and must be present in the header row.

First Name,Last Name,Primary Email,Secondary Email,Web Page 1,Organization,Department,Work Phone, Mobile Number, 
Fax Number, Job Title, Work Address, Work Address 2, Work City, Work State, Work ZipCode, 
Work Country
</p>

<form method="post" action="<?php echo build_url('contact', 'uploadcontacts');?>" enctype="multipart/form-data">
<input type="file" name="import" />
<input type="submit" value="Import" />
</form>
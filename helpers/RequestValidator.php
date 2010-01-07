<?php

/**
 * Outputs a validation tag which can be read by the november system
 * to ensure that the passed in request is valid
 */
class Helper_RequestValidator
{
    public function RequestValidator()
    {
        $token = za()->getSession()->novemberValidationToken;
        if ($token == null) {
            $token = md5(mt_rand());
            za()->getSession()->novemberValidationToken = $token;
        }

        ?>
			<input type="hidden" name="__validation_token" value="<?php echo $token ?>" />
<?php
	}
}

?>
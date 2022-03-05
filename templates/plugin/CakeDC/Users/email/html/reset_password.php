<table border="0" cellpadding="0" cellspacing="0" width="100%">
    <tr>
        <td style="padding: 50px 0 30px 0;">
            <table align="center" border="0" cellpadding="0" cellspacing="0" width="590" style="border: 1px solid #FFFFFF; border-collapse: collapse;">
                <tr>
                    <td align="center" bgcolor="#FFFFFF" style="color: #222222; font-size: 18px; font-weight: bold; font-family: 'Open Sans', sans-serif;">
                        <img src="https://io500.org/img/logo.png" alt="IO500" style="display: block;" />
                    </td>
                </tr>
                <tr>
                    <td bgcolor="#FFFFFF" style="padding: 40px 30px 40px 30px;">
                        <table border="0" cellpadding="0" cellspacing="0" width="100%">
                            <tr>
                                <td style="color: #222222; font-family: 'Open Sans', sans-serif; font-size: 14px;">
                                    <?php echo __d('Hi {0}', isset($first_name) ? $first_name : $username); ?>,
                                </td>
                            </tr>
                            <tr>
                                <td style="padding: 20px 0 0 0; color: #222222; font-family: 'Open Sans', sans-serif; font-size: 12px; line-height: 20px;">
                                    <p>
                                        <?php echo __('Someone (hopefully you) has requested a password reset on your account. Please confirm that you requested this password reset by clicking on the link below.'); ?>
                                    </p>
                                    <p style="text-align: center; margin: 40px 0 40px 0;">
                                        <a href="<?php echo $this->Url->build($activationUrl); ?>" style="background: #cc1717; color: #ffffff; display: inline-block; padding: 10px 30px 10px 30px; text-decoration: none; border-radius: 3px; width: 25%; font-weight: bold;"><?php echo __('RESET PASSWORD!'); ?></a>
                                    </p>
                                    <p>
                                        <?php echo __('If you did not request this password reset, you can ignore this email. Your password will remain the same.'); ?>
                                    </p>
                                </td>
                            </tr>
                            <tr>
                                <td style="color: #222222; font-family: 'Open Sans', sans-serif; font-size: 12px; padding-top: 10px;">
                                    <b>IO500 Committee</b>
                                </td>
                            </tr>
                        </table>
                    </td>
                </tr>
                <tr>
                    <td bgcolor="#FFFFFF" style="padding: 30px 30px 30px 30px; border: #FFFFFF solid 1px;">
                        <table border="0" cellpadding="0" cellspacing="0" width="100%">
                            <tr>
                                <td style="color: #777777; font-family: 'Open Sans', sans-serif; font-size: 12px; text-align: center;">
                                    <p>
                                        <?php echo date('Y'); ?> &copy; IO500 Foundation<br/>
                                        <b>io500.org</b>
                                    </p>
                                </td>
                            </tr>
                            <tr>
                                <td>
                                    <p style="color: #777777; font-family: 'Open Sans', sans-serif; font-size: 10px; text-align: center;">
                                        <?php echo __d(
                                            'cake_d_c/users',
                                            "If the link is not correctly displayed, please copy the following address in your web browser: {0}",
                                            $this->Url->build($activationUrl)
                                        ) ?>
                                    </p>
                                </td>
                            </tr>
                        </table>
                    </td>
                </tr>
            </table>
        </td>
    </tr>
</table>
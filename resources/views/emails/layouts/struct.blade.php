<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html xmlns="http://www.w3.org/1999/xhtml">
<head>
    <meta http-equiv="Content-Type" content="text/html; charset=UTF-8"/>
    <title>NodCredit Email</title>
    <meta name="viewport" content="width=device-width, initial-scale=1.0"/>
</head>
<body style="margin: 0; padding: 0; background-color: #F4F4F4;">
<table
        bgcolor="#F4F4F4"
        border="0"
        cellpadding="0"
        cellspacing="0"
        width="100%"
>
    <tr>
        <td style="padding: 10px 0 30px 0;">
            <table
                    align="center"
                    border="0"
                    cellpadding="0"
                    cellspacing="0"
                    width="600"
                    style="border: 0px solid #cccccc; border-collapse: collapse;"
            >
                <tr>
                    <td style="padding: 40px 0px; color: #153643; font-size: 28px; font-weight: bold; font-family: Arial, sans-serif; text-align: center">
                        <a href="{{ route('home') }}">
                            <img src="{{ asset('assets/emails/img/logo.png') }}" alt="NodCredit" style="display: inline-block;" />
                        </a>
                    </td>
                </tr>
                <tr>
                    <td bgcolor="#ffffff" style="padding: 40px 30px 40px 30px;">
                        <table border="0" cellpadding="0" cellspacing="0" width="100%">
                            <tr>
                                <td style="padding: 10px 0; color: #4B535E; font-family: Arial, sans-serif; font-size: 16px; line-height: 28px;">
                                    @yield('content')
                                </td>
                            </tr>
                        </table>
                    </td>
                </tr>
                <tr>
                    <td bgcolor="#0668E3" style="padding: 30px 30px 30px 30px; margin-top: 50px;">
                        <table>
                            <tr>
                                <td width="100%" style="color: rgba(255, 255, 255, 0.67); font-family: Arial, sans-serif; font-size: 12px; line-height: 14px;">
                                    Get access to loans and invest with our app today, available on
                                    <strong><a href="https://play.google.com/store/apps/details?id=com.nodcredit.app" style="color: rgba(255, 255, 255, 0.9)" >Android</a></strong>
                                </td>
                            </tr>
                        </table>

                        <table width="100%">
                            <tr>
                                <td width="100%">
                                    <hr width="100%" style="margin:16px 0px ;color: rgba(255, 255, 255, 0.28);" />
                                </td>
                            </tr>
                        </table>

                        <table border="0" cellpadding="0" cellspacing="0" width="100%">
                            <tr>
                                <td style="color: #ffffff; font-family: Arial, sans-serif; font-size: 14px;" width="5%">
                                    <a href="{{ route('home') }}">
                                        <img src="{{ asset('assets/emails/img/logo-icon.png') }}" alt="Nodcredit"/>
                                    </a>
                                </td>
                                <td align="right" width="35%">
                                    <table border="0" cellpadding="0" cellspacing="0">
                                        <tr>
                                            <td style="font-family: Arial, sans-serif; font-size: 12px; font-weight: bold;" >
                                                <a href="https://www.facebook.com/Nodcredit/" target="_blank" style="color: #ffffff;">
                                                    <img src="{{ asset('assets/emails/img/facebook.png') }}" alt="Facebook" border="0" />
                                                </a>
                                            </td>
                                            <td style="font-size: 0; line-height: 0;" width="20">
                                                &nbsp;
                                            </td>
                                            <td style="font-family: Arial, sans-serif; font-size: 12px; font-weight: bold;" >
                                                <a href="https://twitter.com/nodcredit/" target="_blank" style="color: #ffffff;">
                                                    <img src="{{ asset('assets/emails/img/twitter.png') }}" alt="Twitter" border="0" />
                                                </a>
                                            </td>
                                            <td style="font-size: 0; line-height: 0;" width="20">
                                                &nbsp;
                                            </td>
                                            <td style="font-family: Arial, sans-serif; font-size: 12px; font-weight: bold;" >
                                                <a href="https://www.linkedin.com/nodcredit" target="_blank" style="color: #ffffff;">
                                                    <img src="{{ asset('assets/emails/img/linkedin.png') }}" alt="LinkedIn" border="0" />
                                                </a>
                                            </td>
                                        </tr>
                                    </table>
                                </td>
                            </tr>
                        </table>
                    </td>
                </tr>
            </table>
        </td>
    </tr>
</table>
</body>
</html>
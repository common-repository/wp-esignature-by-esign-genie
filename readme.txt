=== Plugin Name ===
Contributors: wpesigngenie
Tags: esignature, esign, esignature for wordpress
Requires at least: 4.6
Tested up to: 6.6.1
Stable tag: 4.3
Requires PHP: 5.2.4
License: GPLv2 or later
License URI: https://www.gnu.org/licenses/gpl-2.0.html

eSignature for WordPress by eSign Genie allows you a seamless workflow to connect your wp form with eSign Genie application to sign and send the document instantly.

== Description ==

eSignature for WordPress by eSign Genie allows you a seamless workflow to connect your wp form with eSign Genie application to sign and send the document instantly.

Features:
1. Fully compatible with WordPress
2. Easy and fast way connect your WPForm for eSignature
3. Add and map custom fields from your WPForm to eSign Genie Form
4. Sign documents online or insert e-signature fields to your forms.
5. Secured and authorized access use for signing

== eSign Genie API ==

eSign Genie is an OAuth2 provider. Security implementation is based on the final version of OAuth 2.0 Authorization framework.
Every API request requires access_token as part of the authentication header. 
This is a three step process:
1.	Authentication Process (Developer)
	a.	Obtain an API key
	•	You can obtain the eSign Genie API key provided as client_id in the API section under the account settings.
	b.	Authorize a User
	•	The user is redirected to eSign Genie consent screen from where an auth_code is generated.
	c.	Create an Access Token
	•	The auth_code obtained is required to generate the access_token.
	d.	Optionally, refresh Access Token
	•	Access_token eventually expires thus store the refresh_token returned when creating an access token. Returns a new access_token.
2. Getting client_id,client_secret and Apis Base url, we are using https://www.esigngenie.com/wp-plugin-auth.php Api. 
3.	Developer to Authorize a 3rd Party User (via Developer Application User Interface)
	Your application exchanges the client_id for an auth_code by an HTML “consent” screen.
	Send the authenticating user from your application to eSign Genie OAuth2 request URL. We recommend a button or a link titled “Connect to eSign Genie”.
	GET: Apis Base urloauth2/authorize?client_id=client_id&redirect_uri=redirect_uri&scope=read-write&response_type=code&state=state
	Parameters to be provided by the developer:
	client_id	The client_id found in the API section in your eSign Genie developer account.
	redirect_uri	Your application Redirect URI. 
	If there is a value provided in the eSign Genie Developer Console then it must match that value. 
	Example: https://yourdomain.com
	Scope	Requested permissions. Please use the default value read-write
	response_type	Use value: code
	state	This parameter is used as an anti-CSRF measure.
	Use any random value here which will be returned back to the redirect URL together with authorization code. Then you can validate the value received in response against the value originally submitted.
	Users will see the “consent” screen as following if the user has an eSign Genie account already otherwise he/she will create a sign up:
	 
	When the 3rd Party user clicks “Allow”, eSign Genie redirects the user back to your site (redirect_url) with an authorization code in the parameter “code”.
	{redirect_url}?code=authorization_code&state=state
	Use this authorization code thus obtained to generate the access token for this user for your application.
	Create Access Token for the 3rd Party User
	Your application exchanges the auth_code and client_id for an access token.
	POST: Apis Base url api/oauth2/access_token
	CONTENT TYPE:  application/x-www-form-URLencoded
	Body: grant_type=authorization_code&client_id=client_id&client_secret=client_secret&code=auth_code&redirect_uri=redirect_uri
	grant_type	Use value: authorization_code
	client_id	Use the value from your (developer) account
	client_secret	Use the value from your (developer) account
	code	This value is the authorization code that was received in previous step for the 3rd Party
	redirect_uri	Submit the developer redirect uri that was originally submitted when generating the authorization code in the previous step.

	Response:
	{
	"access_token":"ACCESS_TOKEN",
	"refresh_token":"REFRESH_TOKEN",
	"token_type":"bearer",
	"expires_in":31536000
	}

	Include this access_token in the Authorization header whenever you make an API call.
	Authorization: Bearer ACCESS_TOKEN
	Refer to eSign Genie API guide for further examples of API calls.
	IMPORTANT NOTE: We recommend to store both these access_token as well as refresh_token for this user inside your application storage.
	The expiration time limit of an access token is usually 365 days (31536000 in seconds), once the access token expires you can generate a new access token using the refresh token without requiring user interaction.

	Using Refresh Token
	You can use a refresh token to get new access token after the access token expires. Refresh tokens do not require the account user to log in again.
	To get a new access token using refresh token make a similar request as you would in case of an authorization code, but with some changes in the request parameters to be sent.
	POST: Apis Base url api/oauth2/access_token
	CONTENT TYPE:  application/x-www-form-URLencoded
	Body: grant_type=refresh_token&client_id=client_id&client_secret=client_secret&refresh_token=refresh_token
	grant_type	Use value: refresh_token
	client_id	Use the value from your account
	client_secret	Use the value from your account
	refresh_token	This value is the refresh token that was received in previous step

	Response:
	{
	"access_token":"ACCESS_TOKEN",
	"refresh_token":"REFRESH_TOKEN",
	"token_type":"bearer",
	"expires_in":31536000
	}

	Replace both the access token and refresh token values in your application for this user with the new values received in the response above. And hence use the new access token to make API calls for this user.
	Instead of waiting for an access token to expire, we recommend that you generate a new access token when its expiration time is within 60 minutes, for example. When you first get the access token use the expires_in value for that user to calculate the expiration time and store it for that user in your application.

	Response:
	In the response, you receive an empty body with the response code of 200, indicating that the revoke action was successful.


+Many More

How Does It Work?
This plugin developed by eSign Genie, #1 Rated eSignature software used by companies of all sizes from 60+ countries. Know more information click Here.

eSign Genie eSignatue - Key Benefits:
1. Close deal faster
2. Easy and fast onboarding
3. Secure, auditable and fully compliance 
4. Save cost incurred in paperwork and process
5. Execute contract faster
6. Get approval faster
7. Increase customer onboarding experience

== Installation ==

Steps to install eSignature for WordPress by eSign Genie plugin:

1. Upload the plugin files to the `/wp-content/plugins/eSignature for WordPress by eSign Genie' directory, or install the plugin through the WordPress plugins screen directly. This pluin currently support WPForms only

2. Activate the plugin through the 'Plugins' screen in WordPress

3. Use the Settings->Plugin Name screen to configure the eSignature for WordPress by eSign Genie plugin


Steps to set up and start using eSign Genie:

1. Create an eSign Genie Account
2. Create  a template in eSign Genie that will be mapped with the WPForms: https://www.youtube.com/watch?v=_xJI9f0fFIg&t=82s
3. Install the 'eSignature for WordPress by eSign Genie' plugin
4. Connect with eSign Genie Account
5. Map your WPForms fields with eSign Genie template fields
6. Test your WPForms by filling out and submitting
You will be ready to go-live after these six simple steps


== Screenshots ==

1. This screen shot description corresponds to screenshot-1.jpg.

== Changelog ==

=1.2.2=
* Test with wordpress 6.6.1 version   

=1.2.1=
* Check  

=1.2.0=
* Test with wordpress 5.8.1 version   

= 1.1.12 =

* Update Auto refresh token functionality 


= 1.1.11 =

* Updated plugin display name for wp-admin

= 1.1.10 =

* Updated plugin display name

= 1.1.9 =

* Button CSS changes


= 1.1.8 =

* Active mapping for all same company users


= 1.1.7 =

* Increase Limit of Wpforms dropown list

= 1.1.6 =

* Fixed Wpforms Payment Multiple Items Field value for eSign Genie Template

= 1.1.5 =

* Fixed Wpforms Country Field value for eSign Genie Template

= 1.1.4 =

* Fixed Postal Code issue

= 1.1.3 =

* Fixed Country Seclection issue

= 1.1.2 =

* Change Tool Tips CSS

= 1.1.1 =

* Change Error Message 

= 1.1.0 =

* Mpped Advance Fields of WpForms 


= 1.0.6 =

* Parties phone number format change from our end and put wpforms pro plugin folder location condition   


= 1.0.5 =

* Remove WPForms_ACTIVE condition from wpesg_add_mapping function
* Put a new condition(wpforms directory) for admin_notices hook

= 1.0.4 =

* Remove Extra condition from edit party fields functions


= 1.0.3 =

* Change Embedded SigningSession Parameter

= 1.0.2 =

* Change Test Up to WordPress version 5.4

= 1.0.1 =

* Update Send Email Condition 


= 1.0 =

* First Release

`<?php code(); // goes in backticks ?>`
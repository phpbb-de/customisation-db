<?php
/**
 *
 * @package titania
 * @version $Id$
 * @copyright (c) 2008 phpBB Customisation Database Team
 * @license http://opensource.org/licenses/gpl-license.php GNU Public License
 *
 */

/**
* @ignore
*/
if (!defined('IN_TITANIA'))
{
	exit;
}

//@todo Logged in users only.

titania::add_lang('attachments');
titania::load_object('contribution');

$contrib = new titania_contribution();

$submit = (isset($_POST['submit'])) ? true : false;

$error = array();
$contrib_permalink = '';
$contrib_category = 0;

titania::load_object('attachments');
$attachment = new titania_attachments(TITANIA_DOWNLOAD_CONTRIB, $contrib->contrib_id);

if ($submit)
{
	$contrib->contrib_name 		= utf8_normalize_nfc(request_var('name', '', true));
	$contrib->contrib_desc 		= utf8_normalize_nfc(request_var('description', '', true));
	$contrib_category			= request_var('contrib_category', 0);
	$contrib->contrib_type		= request_var('contrib_type', 0);
	$contrib_permalink			= request_var('permalink', '');

	if (!$contrib->contrib_type)
	{
		$error[] = phpbb::$user->lang['EMPTY_CONTRIB_TYPE'];
	}

	if (!$contrib_category)
	{
		$error[] = phpbb::$user->lang['EMPTY_CATEGORY'];
	}

	if (!$contrib->contrib_name)
	{
		$error[] = phpbb::$user->lang['EMPTY_CONTRIB_NAME'];
	}

	if (!$contrib->contrib_desc)
	{
		$error[] = phpbb::$user->lang['EMPTY_CONTRIB_DESC'];
	}

	if (!$contrib_permalink)
	{
		$error[] = phpbb::$user->lang['EMPTY_CONTRIB_PERMALINK'];
	}

	if (!validate_permalink($contrib_permalink))
	{
		$error[] = phpbb::$user->lang['CONTRIB_NAME_EXISTS'];
	}

	if (!sizeof($error))
	{
		$contrib->contrib_user_id 		= phpbb::$user->data['user_id'];
		$contrib->contrib_name_clean 	= utf8_clean_string($contrib_permalink);

		$contrib->submit();

		$sql = 'INSERT INTO ' . TITANIA_CONTRIB_IN_CATEGORIES_TABLE . ' ' . phpbb::$db->sql_build_array('INSERT', array(
			'contrib_id' 	=> $contrib->contrib_id,
			'category_id'	=> $contrib_category,
		));

		phpbb::$db->sql_query($sql);

		meta_refresh(3, $contrib->get_url());
		
		titania::error_box('SUCCESS', 'CONTRIB_CREATED', TITANIA_SUCCESS);
	}
}

$template->assign_vars(array(
	'U_ACTION'					=> titania::$url->build_url('contributions/create'),

	'ERROR_MSG'					=> (sizeof($error)) ? implode('<br />', $error) : false,

	'CONTRIB_NAME'				=> $contrib->contrib_name,
	'CONTRIB_PERMALINK'			=> $contrib_permalink,
	'CONTRIB_DESC'				=> $contrib->get_text(true),
	'CONTRIB_TYPE_SELECT'		=> contrib_type_select($contrib->contrib_type),
	'CONTRIB_CATEGORY_SELECT'	=> contrib_category_select($contrib_category),
));

titania::page_header('CREATE_CONTRIBUTION');
titania::page_footer(true, 'contributions/contribution_create.html');
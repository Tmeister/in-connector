<?php

/**
 * The public-facing functionality of the plugin.
 *
 * @link       https://enriquechavez.co
 * @since      1.0.0
 *
 * @package    In_Connector
 * @subpackage In_Connector/public
 */

/**
 * The public-facing functionality of the plugin.
 *
 * Defines the plugin name, version, and two examples hooks for how to
 * enqueue the admin-specific stylesheet and JavaScript.
 *
 * @package    In_Connector
 * @subpackage In_Connector/public
 * @author     Enrique Chávez <noone@tmeister.net>
 */
class In_Connector_Public
{

    /**
     * The ID of this plugin.
     *
     * @since    1.0.0
     * @access   private
     * @var      string    $plugin_name    The ID of this plugin.
     */
    private $plugin_name;

    /**
     * The version of this plugin.
     *
     * @since    1.0.0
     * @access   private
     * @var      string    $version    The current version of this plugin.
     */
    private $version;

    /**
     * Initialize the class and set its properties.
     *
     * @since    1.0.0
     * @param      string    $plugin_name       The name of the plugin.
     * @param      string    $version    The version of this plugin.
     */
    public function __construct($plugin_name, $version)
    {

        $this->plugin_name = $plugin_name;
        $this->version     = $version;

    }

    /**
     * Creating the ENDPOINT
     **/

    public function create_end_points()
    {

        add_rewrite_endpoint('api', EP_ROOT);

    }

    public function verify_end_point()
    {

        global $wp_query;

        if (!isset($wp_query->query_vars['api'])) {

            return;
        }

        $call = $wp_query->query_vars['api'];

        switch ($call) {

            case 'add-user-email':

                $this->add_user_to_mailing();

                break;

            case 'send-user-results':

                $this->send_user_results();

                break;

        }

    }

    public function send_user_results()
    {
        $data        = $this->get_post_data();
        $infusion_id = false;

        if (isset($data['email'])) {
            $user_email = sanitize_email($data['email']);
        }

        $email_template = $data['template'];
        $email_subject  = !empty($data['subject']) ? $data['subject'] : 'Results';
        $referer        = $data['referer'];
        $suffix         = isset($data['suffix']) ? $data['suffix'] : false;
        $from_name      = isset($data['from_name']) ? $data['from_name'] : 'NoHatU';
        $from_email     = isset($data['from_email']) ? $data['from_email'] : 'team@nohatdigital.com';
        $from_address   = $from_name . ' <' . $from_email . '>';
        $email_template = str_replace('<course_url>', $referer, $email_template);
        $question_tags  = ($data['tags']) ? $data['tags'] : false;

        if (isset($data['infusion_id'])) {
            $infusion_id = intval($data['infusion_id']);
        } else {
            $user = InfusionProxy::getInstance()->getApp()->findByEmail($user_email, array('Id'));
            if ($user) {
                $infusion_id = $user[0]['Id'];
            } else {
                $infusion_id = $this->add_user_to_infusion($user_email);
            }
        }

        $send = $this->send_email_via_infusion($infusion_id, $from_address, $email_template, $email_subject);
        //error_log('Enviado => ' . $send);
        if ($send) {
            if ($suffix) {
                $cat_id = $this->get_infusion_cat_id('', $suffix);
            } else {
                $cat_id = $this->get_infusion_cat_id('Emailed results', '');
            }

            $added = InfusionProxy::getInstance()->getApp()->grpAssign($infusion_id, $cat_id);
            //error_log(print_r($added, true));
            foreach ($question_tags as $q) {
                //error_log('=> ' . $q['tag'] . ' => ' . $q['answer']);
                $subcat_id = $this->get_infusion_cat_id('', $suffix . ' - ' . $q['tag']);
                //error_log('QUESTION TAG ' . $subcat_id);
                $added = InfusionProxy::getInstance()->getApp()->grpAssign($infusion_id, $subcat_id);
            }

            $this->json_output(array('status' => 'success'));
        } else {
            $this->json_output(array('status' => 'fail'));
        }
    }
    public function add_user_to_mailing()
    {

        $data = $this->get_post_data();

        if (isset($data['email'])) {

            $user_email = sanitize_email($data['email']);

        }

        $suffix = isset($data['suffix']) ? $data['suffix'] : false;

        $infusion_id = $this->add_user_to_infusion($user_email, $suffix);

        $this->json_output(array('user_id' => $infusion_id, 'status' => 'success'));

    }

    public function add_user_to_infusion($email, $suffix = false)
    {

        /**
         * Verify if the email already exist
         */
        $user = InfusionProxy::getInstance()->getApp()->findByEmail($email, array('Id'));

        if ($user) {
            $infusion_id = $user[0]['Id'];
        } else {
            $infusion_id = InfusionProxy::getInstance()->getApp()->addCon(array('Email' => $email));
        }

        if (!intval($infusion_id)) {
            echo json_encode(array('status' => 'fail', 'message' => 'Error adding user to InfusionSoft'));
            die();
        }
        if ($suffix) {
            $cat_id = $this->get_infusion_cat_id('', $suffix);
        } else {
            $cat_id = $this->get_infusion_cat_id('Mailing List', '');
        }
        $added = InfusionProxy::getInstance()->getApp()->grpAssign($infusion_id, $cat_id);
        return $infusion_id;

    }
    /**
     * Find the Stepify Category
     * @param  [String] $tag    InfusionSoft Tag Name
     * @param  [mixed] $suffix Editor Suffix tracking
     */
    private function get_infusion_cat_id($tag, $suffix)
    {
        $cat_name = $suffix ? $tag . '' . $suffix : $tag;
        //error_log('Buscando |' . $cat_name . '|');
        $query  = array('GroupName' => $cat_name);
        $cat_id = InfusionProxy::getInstance()->getApp()->dsQuery('ContactGroup', 1, 0, $query, array('Id'));
        if (!$cat_id) {
            //Category not found!!!
            $query     = array('CategoryName' => 'Stepify');
            $parent_id = InfusionProxy::getInstance()->getApp()->dsQuery('ContactGroupCategory', 1, 0, $query, array('Id'));
            if (!$parent_id) {
                error_log('No se encontro parentID');
                return false;
            }
            $new_cat = array('GroupCategoryId' => $parent_id[0]['Id'], 'GroupName' => $cat_name);
            $cat_id  = InfusionProxy::getInstance()->getApp()->dsAdd('ContactGroup', $new_cat);
            if (!$cat_id) {
                error_log('Ahora si mamo TODO');
                return false;
            }
        } else {
            $cat_id = $cat_id[0]['Id'];
        }
        //error_log('Cat_ID');
        //error_log(print_r($cat_id, true));
        return $cat_id;
    }

    private function send_email_via_infusion($infusion_user, $from, $message, $subject)
    {

        //error_log('=> ' . $from);
        //error_log('=> ' . $infusion_user);

        $infusion = InfusionProxy::getInstance()->getApp();

        $mail = $infusion->sendEmail(
            array($infusion_user),
            $from,
            '~Contact.Email~',
            '',
            '',
            'Multipart',
            $subject,
            $message,
            $message
        );

        return $mail;

    }

    private function get_post_data()
    {

        $request = $_SERVER['REQUEST_METHOD'];

        if ($request == 'POST') {

            $data = json_decode(file_get_contents('php://input'), true);

            if (empty($data)) {

                $this->json_output(array('error' => 'The request is empty'));

            } else {

                return $data;
            }

        } else {

            $this->json_output(array('error' => 'The request should be a POST request'));

        }

    }

    private function json_output($data)
    {

        header('Access-Control-Allow-Origin: *');
        header('Access-Control-Allow-Headers: accept, access-control-allow-headers, content-type');
        header('Content-Type: application/json');

        echo json_encode($data);

        die();

    }

}

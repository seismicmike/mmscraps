<?php
namespace Drupal\search404\Form;

use Drupal\Core\Form\ConfigFormBase;
use Drupal\Core\Form\FormStateInterface;

/**
 * Configure settings for search404.
 */
class Search404Settings extends ConfigFormBase {

  /**
   * {@inheritdoc}
   */
  protected function getEditableConfigNames() {
    return array('system.site');
  }

  /**
   * {@inheritdoc}
   */
  public function getFormId() {
    return 'search404_settings';
  }

  /**
   * {@inheritdoc}
   */
  public function buildForm(array $form, FormStateInterface $form_state) {
    $form['search404_jump'] = array(
      '#type' => 'checkbox',
      '#title' => t('Jump directly to the search result when there is only one result'),
      '#description' => t('Works only with Core, Apache Solr, Lucene and Xapian searches. An HTTP status of 301 or 302 will be returned for this redirect.'),
      '#default_value' => \Drupal::config('search404.settings')->get('search404_jump'),
    );
    $form['search404_first'] = array(
      '#type' => 'checkbox',
      '#title' => t('Jump directly to the first search result even when there are multiple results'),
      '#description' => t('Works only with Core, Apache Solr, Lucene and Xapian searches. An HTTP status of 301 or 302 will be returned for this redirect.'),
      '#default_value' => \Drupal::config('search404.settings')->get('search404_first'),
    );
    $form['search404_do_google_cse'] = array(
      '#type' => 'checkbox',
      '#title' => t('Do a Google CSE Search instead of a Drupal Search when a 404 occurs'),
      '#description' => t('Requires Google CSE and Google CSE Search modules to be enabled.'),
      '#attributes' => \Drupal::moduleHandler()->moduleExists('google_cse') ? array() : array('disabled' => 'disabled'),
      '#default_value' => \Drupal::config('search404.settings')->get('search404_do_google_cse'),
    );
    $form['search404_do_search_by_page'] = array(
      '#type' => 'checkbox',
      '#title' => t('Do a "Search by page" Search instead of a Drupal Search when a 404 occurs'),
      '#description' => t('Requires "Search by page" module to be enabled.'),
      '#attributes' => \Drupal::moduleHandler()->moduleExists('search_by_page') ? array() : array('disabled' => 'disabled'),
      '#default_value' => \Drupal::config('search404.settings')->get('search404_do_search_by_page'),
    );
    // Custom search path implementation.
    $form['search404_do_custom_search'] = array(
      '#type' => 'checkbox',
      '#title' => t('Do a "Search" with custom path instead of a Drupal Search when a 404 occurs'),
      '#description' => t('Redirect the user to a Custom search path to be entered below. Can be used to open a view with path parameter.'),
      '#default_value' => \Drupal::config('search404.settings')->get('search404_do_custom_search'),
    );
    $form['search404_custom_search_path'] = array(
      '#type' => 'textfield',
      '#title' => t('Custom search path'),
      '#description' => t('The custom search path: example: myownsearch/@keys. The token "@keys" will be replaced with the search keys from the URL.'),
      '#default_value' => \Drupal::config('search404.settings')->get('search404_custom_search_path'),
    );
    // Added for having a 301 redirect instead of the standard 302
    // (offered by the drupal_goto) than Core, Apache Solr,
    // Lucene and Xapian. Can this even be done? Meta refresh?
    $form['search404_redirect_301'] = array(
      '#type' => 'checkbox',
      '#title' => t('Use a 301 Redirect instead of 302 Redirect'),
      '#description' => t('This applies when the option to jump to first result is enabled and also for search404 results pages other than for Core, Apache Solr, Lucene and Xapian.'),
      '#default_value' => \Drupal::config('search404.settings')->get('search404_redirect_301'),
    );
    // Added for preventing automatic search for large sites.
    $form['search404_skip_auto_search'] = array(
      '#title' => t('Disable auto search'),
      '#description' => t('Disable automatically searching for the keywords when a page is not found and instead show the populated search form with the keywords. Useful for large sites to reduce server loads.'),
      '#type' => 'checkbox',
      '#default_value' => \Drupal::config('search404.settings')->get('search404_skip_auto_search'),
    );
    // Disable the drupal error message when showing search results.
    $form['search404_disable_error_message'] = array(
      '#title' => t('Disable error message'),
      '#type' => 'checkbox',
      '#description' => t('Disable the Drupal error message when search results are shown on a 404 page.'),
      '#default_value' => \Drupal::config('search404.settings')->get('search404_disable_error_message'),
    );

    // To add custom error message.
    $form['search404_custom_error_message'] = array(
      '#title' => t('Custom error message'),
      '#type' => 'textfield',
      '#placeholder' => 'For example, Invalid search for @keys, Sorry the page does not exist, etc.',
      '#description' => t('A custom error message instead of default Drupal message, that should be displayed when search results are shown on a 404 page, use "@keys" to insert the searched key value if necessary.'),
      '#default_value' => \Drupal::config('search404.settings')->get('search404_custom_error_message'),
    );

    $form['advanced'] = array(
      '#type' => 'fieldset',
      '#title' => t('Advanced settings'),
      '#collapsible' => TRUE,
      '#collapsed' => TRUE,
    );
    $form['advanced']['search404_use_or'] = array(
      '#type' => 'checkbox',
      '#title' => t('Use OR between keywords when searching'),
      '#default_value' => \Drupal::config('search404.settings')->get('search404_use_or'),
    );
    $form['advanced']['search404_use_search_engine'] = array(
      '#type' => 'checkbox',
      '#title' => t('Use auto-detection of keywords from search engine referer'),
      '#description' => t('This feature will conduct a search based on the query string got from a search engine if the URL of the search result points to a 404 page in the current website. Currently supported search engines: Google, Yahoo, Altavista, Lycos, Bing and AOL.'),
      '#default_value' => \Drupal::config('search404.settings')->get('search404_use_search_engine'),
    );
    $form['advanced']['search404_ignore'] = array(
      '#type' => 'textarea',
      '#title' => t('Words to ignore'),
      '#description' => t('These words will be ignored from the search query. Separate words with a space, e.g.: "and or the".'),
      '#default_value' => \Drupal::config('search404.settings')->get('search404_ignore'),
    );
    $form['advanced']['search404_ignore_paths'] = array(
      '#type' => 'textarea',
      '#title' => t('Specific paths to ignore'),
      '#description' => t('These paths will be ignored. Site default "Page not found" page will be displayed. Enter one path per line. The "*" character is a wildcard. Example paths are blog for the blog page and blog/* for every personal blog.'),
      '#default_value' => \Drupal::config('search404.settings')->get('search404_ignore_paths', ''),
    );
    $form['advanced']['search404_ignore_extensions'] = array(
      '#type' => 'textfield',
      '#title' => t('Extensions to ignore'),
      '#description' => t('These extensions will be ignored from the search query, e.g.: http://www.example.com/invalid/page.php will only search for "invalid page". Separate extensions with a space, e.g.: "htm html php". Do not include leading dot.'),
      '#default_value' => \Drupal::config('search404.settings')->get('search404_ignore_extensions'),
    );
    $form['advanced']['search404_ignore_query'] = array(
      '#type' => 'textfield',
      '#title' => t('Extensions to abort search'),
      '#description' => t('A search will not be performed for a query ending in these extensions. Separate extensions with a space, e.g.: "gif jpg jpeg bmp png". Do not include leading dot.'),
      '#default_value' => \Drupal::config('search404.settings')->get('search404_ignore_query'),
    );
    $form['advanced']['search404_regex'] = array(
      '#type' => 'textfield',
      '#title' => t('PCRE filter'),
      '#description' => t('This regular expression will be applied to filter all queries. The parts of the path that match the expression will be EXCLUDED from the search. You do NOT have to enclose the regex in forward slashes when defining the PCRE. e.g.: use "[foo]bar" instead of "/[foo]bar/". On how to use a PCRE Regex please refer <a href="http://php.net/pcre">PCRE pages in the PHP Manual</a>.'),
      '#default_value' => \Drupal::config('search404.settings')->get('search404_regex'),
    );
    // Show custom title for the 404 search results page.
    $form['advanced']['search404_page_title'] = array(
      '#type' => 'textfield',
      '#title' => t('Custom page title'),
      '#description' => t('You can enter a value that will displayed at the title of the webpage e.g. "Page not found".'),
      '#default_value' => \Drupal::config('search404.settings')->get('search404_page_title'),
    );
    // Show custom text below the search form for the 404 search
    // results page.
    $form['advanced']['search404_page_text'] = array(
      '#type' => 'textarea',
      '#title' => t('Custom page text'),
      '#default_value' => \Drupal::config('search404.settings')->get('search404_page_text'),
      '#description' => t('You can enter a custom text message that can be displayed at the top of the search results, HTML formatting can be used.'),
    );

    // Add a redirect url option for handling empty results display.
    $form['advanced']['search404_page_redirect'] = array(
      '#title' => t('Add a redirection url for empty search results.'),
      '#type' => 'textfield',
      '#placeholder' => 'For example, /node, /node/10, etc.',
      '#description' => t('You can enter a valid url with a leading "/" to display instead of empty result.'),
      '#default_value' => \Drupal::config('search404.settings')->get('search404_page_redirect'),
    );

    // Helps reset the site_404 variable to search404 in case the
    // user changes it manually.
    $form['site_404'] = array(
      '#type' => 'hidden',
      '#value' => 'search404',
    );
    // Tell the user about the site_404 issue.
    $form['search404_variable_message'] = array(
      '#type' => 'markup',
      '#prefix' => '<p>',
      '#suffix' => '</p>',
      '#value' => t('Saving this form will revert the 404 handling on the site to this module.'),
    );

    return parent::buildForm($form, $form_state);
  }

  /**
   * {@inheritdoc}
   */
  public function validateForm(array &$form, FormStateInterface $form_state) {
    parent::validateForm($form, $form_state);
    // Validation for redirect url.
    if (!empty($form_state->getValue('search404_page_redirect'))) {
      $path = $form_state->getValue('search404_page_redirect');
      if (strpos($path, ' ') === 0) {
        $form_state->setErrorByName('search404_page_redirect', t('Invalid url : Redirect url should not be a space or not start with a space.'));
      }
      if (strpos($path, '/') !== 0) {
        $form_state->setErrorByName('search404_page_redirect', t('Invalid url : Redirect url should be start with a slash.'));
      }
    }
    // Validation for custom search path.
    if (!empty($form_state->getValue('search404_do_custom_search')) &&
    !empty($form_state->getValue('search404_custom_search_path'))) {
      $custom_path = $form_state->getValue('search404_custom_search_path');

      if (empty(preg_match("/\/@keys$/", $custom_path))) {
        $form_state->setErrorByName('search404_custom_search_path', t('Custom search path should be ends with search key pattern "/@keys".'));
      }
      if (strpos($custom_path, '/') === 0) {
        $form_state->setErrorByName('search404_page_redirect', t('Custom search path should not be start with a slash.'));
      }
    }
  }

  /**
   * {@inheritdoc}
   */
  public function submitForm(array &$form, FormStateInterface $form_state) {
    $this->configFactory()->getEditable('search404.settings')
      ->set('search404_redirect_301', $form_state->getValue('search404_redirect_301'))
      ->set('search404_do_google_cse', $form_state->getValue('search404_do_google_cse'))
      ->set('search404_do_search_by_page', $form_state->getValue('search404_do_search_by_page'))
      ->set('search404_first', $form_state->getValue('search404_first'))
      ->set('search404_jump', $form_state->getValue('search404_jump'))
      ->set('search404_use_or', $form_state->getValue('search404_use_or'))
      ->set('search404_ignore', $form_state->getValue('search404_ignore'))
      ->set('search404_ignore_paths', $form_state->getValue('search404_ignore_paths'))
      ->set('search404_ignore_query', $form_state->getValue('search404_ignore_query'))
      ->set('search404_ignore_extensions', $form_state->getValue('search404_ignore_extensions'))
      ->set('search404_page_text', $form_state->getValue('search404_page_text'))
      ->set('search404_page_title', $form_state->getValue('search404_page_title'))
      ->set('search404_regex', $form_state->getValue('search404_regex'))
      ->set('search404_skip_auto_search', $form_state->getValue('search404_skip_auto_search'))
      ->set('search404_use_search_engine', $form_state->getValue('search404_use_search_engine'))
      ->set('search404_disable_error_message', $form_state->getValue('search404_disable_error_message'))
      ->set('search404_do_custom_search', $form_state->getValue('search404_do_custom_search'))
      ->set('search404_custom_search_path', $form_state->getValue('search404_custom_search_path'))
      ->set('search404_custom_error_message', $form_state->getValue('search404_custom_error_message'))
      ->set('search404_page_redirect', $form_state->getValue('search404_page_redirect'))
      ->save();
    parent::submitForm($form, $form_state);
  }

}

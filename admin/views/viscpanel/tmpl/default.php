<?php
/**
 * viscpanel default view for Visforms
 *
 * @author       Aicha Vack
 * @package      Joomla.Administrator
 * @subpackage   com_visforms
 * @link         http://www.vi-solutions.de 
 * @license      GNU General Public License version 2 or later; see license.txt
 * @copyright    2012 vi-solutions
 * @since        Joomla 1.6 
 */

//no direct access
 defined('_JEXEC') or die('Restricted access');
JHtml::_('bootstrap.framework');
$issub = VisformsAEF::checkAEF(VisformsAEF::$subscription);
$hasAef = VisformsAEF::checkForOneAef();
$isOldSub = VisformsAEF::checkForAllOldSubFeature();
$component = JComponentHelper::getComponent('com_visforms');
$dlid = $component->params->get('downloadid', '');
$gotSubUpdateInfo = $component->params->get('gotSubUpdateInfo', '');
$demoFormInstalled = $component->params->get('demoFormInstalled', '');
$extensiontypetag = ($issub || $isOldSub) ? 'COM_VISFORMS_SUBSCRIPTION' : 'COM_VISFORMS_PAYED_EXTENSION';
?>

    <?php
 if (!empty( $this->sidebar)) : ?>
<div id="j-sidebar-container" class="span2">
    <?php echo $this->sidebar; ?>
</div>
<div id="j-main-container" class="span10">
    <?php else : ?>
<div id="j-main-container"><?php endif; ?>
    <div id="vfcpanel">
        <?php  if (isset($this->update_message)) {echo $this->update_message;} ?>
        <?php if ($gotSubUpdateInfo == '') { ?>
        <?php if ($isOldSub && !$issub) { ?>
            <div class="alert alert-success">
                <form class="pull-right" action="<?php echo JRoute::_($this->gotSubUpdateInfoLink); ?>" method="post">
                    <input class="btn" type="submit" value="<?php echo JText::_("COM_VISFORMS_CPANEL_OLD_SUB_HIDE_UPDATE_MESSAGE"); ?>" />
                    <?php echo JHtml::_('form.token'); ?>
                </form>
                <h3><?php echo JText::sprintf("COM_VISFORMS_CPANEL_OLD_SUB_HEADER", JText::_($extensiontypetag)); ?></h3>
                <p><?php echo JText::_("COM_VISFORMS_CPANEL_OLD_SUB_DESCR"); ?></p>
                <div class="row-fluid"><a href="<?php echo JRoute::_($this->subUpdateMoreInfoLink); ?>" class="btn btn-info" target="_blank"><?php echo JText::_("COM_VISFORMS_CPANAL_ADDITIONAL_FEATURE_COMPARE_VERSIONS"); ?></a> <a href="<?php echo JRoute::_($this->subUpdateInstructionLink); ?>" class="btn btn-warning" target="_blank"><?php echo JText::_("COM_VISFORMS_CPANEL_OLD_SUB_UPDATE_INSTRUCTIONS"); ?></a>
                </div>
            </div>
        <?php } else if ($hasAef && !$issub) { ?>
            <div class="alert alert-success">
                <form class="pull-right" action="<?php echo JRoute::_($this->gotSubUpdateInfoLink); ?>" method="post">
                    <input class="btn" type="submit" value="<?php echo JText::_("COM_VISFORMS_CPANEL_OLD_SUB_HIDE_UPDATE_MESSAGE"); ?>" />
                    <?php echo JHtml::_('form.token'); ?>
                </form>
                <h3><?php echo JText::sprintf("COM_VISFORMS_CPANEL_OLD_SUB_HEADER", JText::_($extensiontypetag)); ?></h3>
                <h3><?php echo JText::_("COM_VISFORMS_CPANEL_HAVE_SINGLE_EXT"); ?></h3>
                <p><?php echo JText::_("COM_VISFORMS_CPANEL_OLD_EXT_DESCR"); ?> <a href="<?php echo JRoute::_($this->extUpdateMoreInfoLink); ?>" class="btn btn-warning" target="_blank"><?php echo JText::_("COM_VISFORMS_CPANAL_ADDITIONAL_FEATURE_COMPARE_VERSIONS"); ?></a></p>
                <h3><?php echo JText::_("COM_VISFORMS_CPANEL_HAVE_OLD_SUB"); ?></h3>
                <p><?php echo JText::_("COM_VISFORMS_CPANEL_OLD_SUB_DESCR"); ?> <a href="<?php echo JRoute::_($this->subUpdateMoreInfoLink); ?>" class="btn btn-warning" target="_blank"><?php echo JText::_("COM_VISFORMS_CPANAL_ADDITIONAL_FEATURE_COMPARE_VERSIONS"); ?></a></p>
            </div>
        <?php } ?>
        <?php } ?>
        <div class="row-fluid">
            <div class="span6">
                <h1><?php echo JText::_('COM_VISFORMS_SUBMENU_CPANEL_LABEL'); ?></h1>
            </div><?php
            if (!empty($this->twitterLink)) { ?>
            <div class="span5">
                <p style="margin-top: 10px;">
                    <a href="<?php echo $this->twitterLink; ?>" target="_blank" style="display: block; width: 36px; height: 36px; background-color: rgb(29, 161, 242); border-radius: 10%;">
                        <svg xmlns="http://www.w3.org/2000/svg" xmlns:xlink="http://www.w3.org/1999/xlink"
                             viewBox="-2 -2 32 32" version="1.1" role="img" aria-labelledby="at-svg-twitter-2"
                             class="at-icon at-icon-twitter"
                             style="fill: rgb(255, 255, 255); width: 28px; height: 28px;"><title id="at-svg-twitter-2">
		                        <?php echo JText::_('COM_VISFORMS_FOLLOW_TWITTER'); ?></title>
                            <g>
                                <path d="M27.996 10.116c-.81.36-1.68.602-2.592.71a4.526 4.526 0 0 0 1.984-2.496 9.037 9.037 0 0 1-2.866 1.095 4.513 4.513 0 0 0-7.69 4.116 12.81 12.81 0 0 1-9.3-4.715 4.49 4.49 0 0 0-.612 2.27 4.51 4.51 0 0 0 2.008 3.755 4.495 4.495 0 0 1-2.044-.564v.057a4.515 4.515 0 0 0 3.62 4.425 4.52 4.52 0 0 1-2.04.077 4.517 4.517 0 0 0 4.217 3.134 9.055 9.055 0 0 1-5.604 1.93A9.18 9.18 0 0 1 6 23.85a12.773 12.773 0 0 0 6.918 2.027c8.3 0 12.84-6.876 12.84-12.84 0-.195-.005-.39-.014-.583a9.172 9.172 0 0 0 2.252-2.336"
                                      fill-rule="evenodd"></path>
                            </g>
                        </svg>
                    </a>
                </p>
            </div><?php
            } ?>
        </div>
        <div class="row-fluid">
            <div class="span6">
                <h3><?php echo JText::_('COM_VISFORMS_CPANEL_OPERATIIONS_HEADER'); ?></h3>
                <div class="clearfix">
                    <div class="cpanel">
                        <a href="index.php?option=com_visforms&amp;view=visforms"><i class="icon-stack"></i><span><?php echo JText::_('COM_VISFORMS_SUBMENU_FORMS'); ?></span></a>
                    </div>
                    <?php if ($this->canDo->get('core.create')) : ?>
                    <div class="cpanel">
                        <a href="index.php?option=com_visforms&amp;task=visform.add" ><i class="icon-file-plus"></i><span><?php echo JText::_('COM_VISFORMS_FORM_NEW'); //echo (new JLayoutFile('div.quickstart_help_element'))->render(array('step' => 1, 'tag' => 'span'));?></span></a>
                    </div>
                    <?php endif; ?>
                    <?php if (JFactory::getUser()->authorise('core.admin', 'com_visforms')) : ?>
                    <div class="cpanel">
                        <a href="<?php echo $this->preferencesLink; ?>" ><i class="icon-options"></i><span><?php echo JText::_('JTOOLBAR_OPTIONS'); ?></span></a>
                    </div>
                    <?php endif; ?>
                    <?php if ($this->canDo->get('core.edit.css')) : ?>
                    <div class="cpanel">
                        <a href="index.php?option=com_visforms&amp;task=viscpanel.edit_css" ><i class="icon-editcss"></i><span><?php echo JText::_('COM_VISFORMS_EDIT_CSS'); ?></span></a>
                    </div>
                    <?php endif; ?>
                </div>

            </div>
            <div class="span5">
                <h3><?php echo JText::_('COM_VISFORMS_CPANEL_INFO_SUPPORT_HEADER'); ?></h3>
                <div class="clearfix">
                    <div class="cpanel">
                        <a href="<?php echo $this->documentationLink; ?>" target="_blank"><i class="icon-info-circle"></i><span><?php echo JText::_('COM_VISFORMS_CPANEL_DOCUMENTATION_BUTTON_LABEL');?></span></a>
                    </div>
                    <div class="cpanel">
                        <a href="<?php echo $this->forumLink; ?>" target="_blank"><i class="icon-question-circle"></i><span><?php echo JText::_('COM_VISFORMS_CPANEL_FORUM_BUTTON_LABEL');?></span></a>
                    </div>
                </div>

            </div>
        </div>
        <div class="row-fluid">
            <div class="span6">
                <?php if ((empty($issub)) && (empty($hasAef))) : ?>
                <h3><?php echo JText::_('COM_VISFORMS_CPANEL_ADDITIONAL_FEATURE_HEADER'); ?></h3>
                <div id="subscribe" class="alert alert-block alert-info">
                    <p class="text-center"><?php echo JText::_('COM_VISFORMS_CPANAL_ADDITIONAL_FEATURE_TEXT'); ?></p>
                    <p class="text-center visible-desktop"><?php echo JText::_('COM_VISFORMS_CPANAL_ADDITIONAL_FEATURE_LIST'); ?></p>
                    <p class="text-center" style="margin-top: 20px"><a href="<?php echo $this->versionCompareLink; ?>" target="_blank" class="btn btn-small"><?php echo JText::_('COM_VISFORMS_CPANAL_ADDITIONAL_FEATURE_COMPARE_VERSIONS'); ?></a>
                    <a href="<?php echo $this->buySubsLink; ?>" target="_blank" class="btn btn-small"><?php echo JText::_('COM_VISFORMS_CPANAL_ADDITIONAL_FEATURE_BUY_SUBSCRIPTION'); ?></a></p>
                </div>
                <?php endif; ?>
                <?php if ((!empty($issub)) || (!empty($hasAef))) : ?>
                    <h3><?php echo JText::sprintf('COM_VISFORMS_CPANEL_MANAGE_SUBSCRIPTION_HEADER', JText::_($extensiontypetag)); ?></h3>
                    <div class="clearfix">
                        <div class="cpanel">
                            <a href="#downloadid" data-toggle="modal"><i class="icon-unlock "></i><span><?php echo JText::_('COM_VISFORMS_CPANEL_UPDATE_BUTTON_LABEL'); ?></span></a>
                        </div>
                        <div class="cpanel">
                            <a href=<?php echo $this->dlidInfoLink; ?>" target="_blank"><i class="icon-eye-open "></i><span><?php echo JText::_('COM_VISFORMS_CPANEL_MANAGE_BUTTON_LABEL'); ?></span></a>
                        </div><?php
                        // todo enable if
                        if (empty($demoFormInstalled) && JFactory::getUser()->authorise('core.create', 'com_visforms')) { ?>
                            <div class="cpanel">
                            <a href="<?php echo $this->installPdfDemoFormLink; ?>"><i class="icon-drawer"></i><span
                                        style="margin-top:0;"><?php echo JText::_('COM_VISFORMS_CPANEL_INSTALL_PDF_DEMO_LABEL'); ?></span></a>
                            </div><?php
                        } ?>
                    </div>
                <?php endif; ?>
            </div>
            <div class="span5">

                <h3><?php echo JText::_('COM_VISFORMS_CPANEL_CONTRIBUTE_HEADER'); ?></h3>
                <div class="clearfix">
                    <div class="cpanel">
                        <a href="http://extensions.joomla.org/extensions/contacts-and-feedback/forms/23899" target="_blank"><i class="icon-star"></i><span><?php echo JText::_('COM_VISFORMS_CPANEL_REVIEW_BUTTON_LABEL');?></span></a>
                    </div>
                    <?php if (empty($issub) && (empty($hasAef))) : ?>
                    <div class="cpanel">
                        <a href="<?php echo $this->donateLink; ?>" target="_blank"><i class="icon-credit"></i><span><?php echo JText::_('COM_VISFORMS_CPANEL_DONATE_BUTTON_LABEL');?></span></a>
                    </div>
                    <?php endif; ?>
                </div>

            </div>
        </div>
        <div class="row-fluid">
            <div class="span11">
                    <h3><?php echo JText::_('COM_VISFORMS_HELP_GETTING_STARTED_HEADER'); ?></h3>
                    <div class="accordion" id="first-steps">
                        <div class="accordion-group">
                            <div class="accordion-heading">
                                <a class="accordion-toggle" data-toggle="collapse" data-parent="#first-steps" href="#createform">
                                    <?php echo JText::_('COM_VISFORMS_CREATE_FORM'); ?>
                                </a>
                            </div>
                            <div id="createform" class="accordion-body collapse">
                                <div class="accordion-inner">
                                    <ul>
                                        <li><?php echo JText::_('COM_VISFORMS_CREATE_FORM_STEP1'); ?></li>
                                        <li><?php echo JText::_('COM_VISFORMS_CREATE_FORM_STEP2'); ?></li>
                                        <li><?php echo JText::_('COM_VISFORMS_CREATE_FORM_STEP3'); ?></li>
                                    </ul>
                                </div>
                            </div>
                        </div>
                        <div class="accordion-group">
                            <div class="accordion-heading">
                                <a class="accordion-toggle" data-toggle="collapse" data-parent="#first-steps" href="#addfields">
                                    <?php echo JText::_('COM_VISFORMS_ADD_FIELDS'); ?>
                                </a>
                            </div>
                            <div id="addfields" class="accordion-body collapse">
                                <div class="accordion-inner">
                                    <ul>
                                        <li><?php echo JText::_('COM_VISFORMS_ADD_FIELDS_STEP1'); ?></li>
                                        <li><?php echo JText::_('COM_VISFORMS_ADD_FIELDS_STEP2'); ?></li>
                                        <li><?php echo JText::_('COM_VISFORMS_ADD_FIELDS_STEP3'); ?></li>
                                        <li><?php echo JText::_('COM_VISFORMS_ADD_FIELDS_STEP4'); ?></li>
                                    </ul>
                                </div>
                            </div>
                        </div>
                        <div class="accordion-group">
                            <div class="accordion-heading">
                                <a class="accordion-toggle" data-toggle="collapse" data-parent="#first-steps" href="#addsubmit">
                                    <?php echo JText::_('COM_VISFORMS_ADD_SUBMIT_BUTTON'); ?>
                                </a>
                            </div>
                            <div id="addsubmit" class="accordion-body collapse">
                                <div class="accordion-inner">
                                    <ul>
                                        <li><?php echo JText::_('COM_VISFORMS_ADD_SUBMIT_BUTTON_STEP1'); ?></li>
                                    </ul>
                                </div>
                            </div>
                        </div>
                        <div class="accordion-group">
                            <div class="accordion-heading">
                                <a class="accordion-toggle" data-toggle="collapse" data-parent="#first-steps" href="#createmenu">
                                    <?php echo JText::_('COM_VISFORMS_FIRST_STEPS_ADD_MENU_ITEM'); ?>
                                </a>
                            </div>
                            <div id="createmenu" class="accordion-body collapse">
                                <div class="accordion-inner">
                                    <ul>
                                        <li><?php echo JText::_('COM_VISFORMS_FIRST_STEPS_ADD_MENU_ITEM_STEP1'); ?></li>
                                        <li><?php echo JText::_('COM_VISFORMS_FIRST_STEPS_ADD_MENU_ITEM_STEP2'); ?></li>
                                    </ul>
                                </div>
                            </div>
                        </div>
                    </div>
            </div>
        </div>
        <div class="row-fluid">
            <div class="span11">
                <h3><?php echo JText::_('COM_VISFORMS_CPANEL_TRANSLATIONS'); ?></h3>
                <p>
                    <a href="<?php echo $this->translationsLink; ?>" target="_blank"><img class="img-bordered" src="<?php echo JUri::root(); ?>/media/com_visforms/img/cs_cz.gif"/></a>
                    <a href="<?php echo $this->translationsLink; ?>" target="_blank"><img class="img-bordered" src="<?php echo JUri::root(); ?>/media/com_visforms/img/el_gr.gif"/></a>
                    <a href="<?php echo $this->translationsLink; ?>" target="_blank"><img class="img-bordered" src="<?php echo JUri::root(); ?>/media/com_visforms/img/es_es.gif"/></a>
                    <a href="<?php echo $this->translationsLink; ?>" target="_blank"><img class="img-bordered" src="<?php echo JUri::root(); ?>/media/com_visforms/img/fr_fr.gif"/></a>
                    <a href="<?php echo $this->translationsLink; ?>" target="_blank"><img class="img-bordered" src="<?php echo JUri::root(); ?>/media/com_visforms/img/he_il.gif"/></a>
                    <a href="<?php echo $this->translationsLink; ?>" target="_blank"><img class="img-bordered" src="<?php echo JUri::root(); ?>/media/com_visforms/img/hu_hu.gif"/></a>
					<a href="<?php echo $this->translationsLink; ?>" target="_blank"><img class="img-bordered" src="<?php echo JUri::root(); ?>/media/com_visforms/img/lt_lt.gif"/></a>
                    <a href="<?php echo $this->translationsLink; ?>" target="_blank"><img class="img-bordered" src="<?php echo JUri::root(); ?>/media/com_visforms/img/nl_nl.gif"/></a>
                    <a href="<?php echo $this->translationsLink; ?>" target="_blank"><img class="img-bordered" src="<?php echo JUri::root(); ?>/media/com_visforms/img/pl_pl.gif"/></a>
                    <a href="<?php echo $this->translationsLink; ?>" target="_blank"><img class="img-bordered" src="<?php echo JUri::root(); ?>/media/com_visforms/img/pt_br.gif"/></a>
                    <a href="<?php echo $this->translationsLink; ?>" target="_blank"><img class="img-bordered" src="<?php echo JUri::root(); ?>/media/com_visforms/img/ru_ru.gif"/></a>
                    <a href="<?php echo $this->translationsLink; ?>" target="_blank"><img class="img-bordered" src="<?php echo JUri::root(); ?>/media/com_visforms/img/sk_sk.gif"/></a>
                    <a href="<?php echo $this->translationsLink; ?>" target="_blank"><img class="img-bordered" src="<?php echo JUri::root(); ?>/media/com_visforms/img/tr_tr.gif"/></a>
                    <a href="<?php echo $this->translationsLink; ?>" target="_blank"><img class="img-bordered" src="<?php echo JUri::root(); ?>/media/com_visforms/img/sr_yu.gif"/></a>
                </p>
            </div>
        </div>
        <?php echo JHtml::_('visforms.creditsBackend'); ?>
    </div>
</div>


<div id="downloadid" class="modal hide fade" tabindex="-1" role="dialog" aria-labelledby="downloadid" aria-hidden="true">
    <form class="form-horizontal" action="<?php echo JRoute::_($this->dlidFormLink); ?>" method="post" style="padding-bottom: 0; margin-bottom:0">
    <div class="modal-header">
        <button type="button" class="close" data-dismiss="modal" aria-hidden="true">×</button>
        <h3><?php echo JText::sprintf('COM_VISFORMS_CPANEL_MODAL_UPDATE_HEADER', JText::_($extensiontypetag));?></h3>
    </div>
    <div class="modal-body">
        <div class="control-group">

                <label class="control-label" style="width: 160px; text-align: right;"><?php echo JText::_('COM_VISFORMS_FIELD_DOWNLOAD_ID_LABEL');?></label>

            <div class="controls">
                <input name="downloadid" type="text" value="<?php echo $dlid; ?>" /><span class="help-inline"><?php echo JText::_('COM_VISFORMS_FIELD_DOWNLOAD_ID_DESC'); ?></span>
            </div>
        </div>
        <div class="accordion" id="dlid">
            <div class="accordion-group">
                <div class="accordion-heading">
                    <a class="accordion-toggle" data-toggle="collapse" data-parent="#dlid" href="#dlid-info">
                        <?php echo JText::_('COM_VISFORMS_FIELD_DOWNLOAD_ID_HEADER'); ?>
                    </a>
                </div>
                <div id="dlid-info" class="accordion-body collapse">
                    <div class="accordion-inner">
                        <p><?php echo JText::sprintf('COM_VISFORMS_DOWNLOAD_ID_DESC', JText::_($extensiontypetag), JText::_('COM_VISFORMS_FIELD_DOWNLOAD_ID_LINK_TEXT'), JText::_($extensiontypetag));?></p>
                        <p><a href="<?php echo $this->dlidInfoLink; ?>" target="_blank"><?php echo JText::_('COM_VISFORMS_FIELD_DOWNLOAD_ID_LINK_TEXT'); ?></a></p>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <div class="modal-footer" style="text-align: left;">
        <input type="submit" class="btn btn-success" value="Submit" />
        <button type="button" class="btn" data-dismiss="modal" aria-hidden="true"><?php echo JText::_('COM_VISFORMS_CLOSE'); ?></button>
    </div>
        <?php echo JHtml::_('form.token'); ?>
    </form>
</div>


<?php
/**
 * @package      Projectfork
 *
 * @author       Tobias Kuhn (eaxs)
 * @copyright    Copyright (C) 2006-2012 Tobias Kuhn. All rights reserved.
 * @license      http://www.gnu.org/licenses/gpl.html GNU/GPL, see LICENSE.txt
 */

defined('_JEXEC') or die();


JHtml::_('behavior.tooltip');
JHtml::_('behavior.multiselect');

$user       = JFactory::getUser();
$uid        = $user->get('id');
$list_order = $this->escape($this->state->get('list.ordering'));
$list_dir   = $this->escape($this->state->get('list.direction'));
$save_order = ($list_order == 'a.ordering');
$total_time = 0;
?>
<form action="<?php echo JRoute::_('index.php?option=com_projectfork&view=timesheet'); ?>" method="post" name="adminForm" id="adminForm">

    <fieldset id="filter-bar">
        <div class="filter-search fltlft">
            <label class="filter-search-lbl" for="filter_search"><?php echo JText::_('JSEARCH_FILTER_LABEL'); ?></label>
            <input type="text" name="filter_search" id="filter_search" value="<?php echo $this->escape($this->state->get('filter.search')); ?>" />

            <button type="submit" class="btn"><?php echo JText::_('JSEARCH_FILTER_SUBMIT'); ?></button>
            <button type="button" onclick="document.id('filter_search').value='';this.form.submit();"><?php echo JText::_('JSEARCH_FILTER_CLEAR'); ?></button>
        </div>
        <div class="filter-select fltrt">
            <select name="filter_published" class="inputbox" onchange="this.form.submit()">
                <option value=""><?php echo JText::_('JOPTION_SELECT_PUBLISHED');?></option>
                <?php echo JHtml::_('select.options', JHtml::_('jgrid.publishedOptions'), 'value', 'text', $this->state->get('filter.published'), true);?>
            </select>

            <?php if ($this->state->get('filter.project')) : ?>
                <select name="filter_author_id" class="inputbox" onchange="this.form.submit()">
                    <option value=""><?php echo JText::_('JOPTION_SELECT_AUTHOR');?></option>
                    <?php echo JHtml::_('select.options', $this->authors, 'value', 'text', $this->state->get('filter.author_id'));?>
                </select>
            <?php endif; ?>
            <?php echo JHtml::_('projectfork.filterProject');?>
        </div>
    </fieldset>
    <div class="clr"></div>

    <table class="adminlist">
        <thead>
            <tr>
                <th width="1%">
                    <input type="checkbox" name="checkall-toggle" value="" title="<?php echo JText::_('JGLOBAL_CHECK_ALL'); ?>" onclick="Joomla.checkAll(this)" />
                </th>
                <th width="5%">
                    <?php echo JHtml::_('grid.sort', 'JSTATUS', 'a.state', $list_dir, $list_order); ?>
                </th>
                <th>
                    <?php echo JHtml::_('grid.sort', 'COM_PROJECTFORK_TASK_TITLE', 'a.task_title', $list_dir, $list_order); ?>
                </th>
                <?php if (!$this->state->get('filter.project')) : ?>
                    <th width="20%">
                        <?php echo JHtml::_('grid.sort', 'JGRID_HEADING_PROJECT', 'project_title', $list_dir, $list_order); ?>
                    </th>
                <?php endif; ?>
                <th width="5%">
                    <?php echo JHtml::_('grid.sort', 'JDATE', 'a.log_date', $list_dir, $list_order); ?>
                </th>
                <th width="10%">
                    <?php echo JHtml::_('grid.sort', 'COM_PROJECTFORK_TIME_SPENT_HEADING', 'a.log_time', $list_dir, $list_order); ?>
                </th>
                <th width="15%">
                    <?php echo JHtml::_('grid.sort', 'JGRID_HEADING_CREATED_BY', 'a.created_by', $list_dir, $list_order); ?>
                </th>
                <th width="15%">
                    <?php echo JHtml::_('grid.sort', 'JGRID_HEADING_ACCESS', 'a.access', $list_dir, $list_order); ?>
                </th>
                <th width="1%" class="nowrap">
                    <?php echo JHtml::_('grid.sort', 'JGRID_HEADING_ID', 'a.id', $list_dir, $list_order); ?>
                </th>
            </tr>
        </thead>
        <tbody>
        <?php foreach ($this->items as $i => $item) :
            $access = ProjectforkHelperAccess::getActions('time', $item->id);

            $total_time += (int) $item->log_time;

            $can_create   = $access->get('time.create');
            $can_edit     = $access->get('time.edit');
            $can_checkin  = ($user->authorise('core.manage', 'com_checkin') || $item->checked_out == $uid || $item->checked_out == 0);
            $can_edit_own = ($access->get('time.edit.own') && $item->created_by == $uid);
            $can_change   = ($access->get('time.edit.state') && $can_checkin);
            ?>
            <tr class="row<?php echo $i % 2; ?>">
                <td class="center">
                    <?php echo JHtml::_('grid.id', $i, $item->id); ?>
                </td>
                <td class="center">
                    <?php echo JHtml::_('jgrid.published', $item->state, $i, 'timesheet.', $can_change, 'cb'); ?>
                </td>
                <td>
                    <?php if ($item->checked_out) : ?>
                        <?php echo JHtml::_('jgrid.checkedout', $i, $item->editor, $item->checked_out_time, 'timesheet.', $can_checkin); ?>
                    <?php endif; ?>
                    <?php if ($can_edit || $can_edit_own) : ?>
                        <a href="<?php echo JRoute::_('index.php?option=com_projectfork&task=time.edit&id=' . $item->id);?>">
                            <?php echo $this->escape($item->task_title); ?>
                        </a>
                    <?php else : ?>
                        <?php echo $this->escape($item->task_title); ?>
                    <?php endif; ?>
                </td>
                <?php if (!$this->state->get('filter.project')) : ?>
                    <td><?php echo $this->escape($item->project_title); ?></td>
                <?php endif; ?>
                <td class="center nowrap">
                    <?php echo JHtml::_('date', $item->log_date, JText::_('DATE_FORMAT_LC4')); ?>
                </td>
                <td class="center nowrap">
                    <?php echo JHtml::_('timesheet.format', $item->log_time); ?>
                </td>
                <td class="center">
                    <?php echo $this->escape($item->author_name); ?>
                </td>
                <td class="center">
                    <?php echo $this->escape($item->access_level); ?>
                </td>
                <td class="center">
                    <?php echo (int) $item->id; ?>
                </td>
            </tr>
            <?php endforeach; ?>
            <tr>
                <td colspan="5" style="text-align: right"><strong><?php echo JText::_('COM_PROJECTFORK_TOTALTIME_SPENT_HEADING'); ?></strong></td>
                <td class="center nowrap"><strong><?php echo JHtml::_('timesheet.format', $total_time); ?></strong></td>
                <td colspan="3"></td>
            </tr>
        </tbody>
        <tfoot>
            <tr>
                <td colspan="9">
                    <?php echo $this->pagination->getListFooter(); ?>
                </td>
            </tr>
        </tfoot>
    </table>


    <input type="hidden" name="boxchecked" value="0" />
    <input type="hidden" name="filter_order" value="<?php echo $list_order; ?>" />
    <input type="hidden" name="filter_order_Dir" value="<?php echo $list_dir; ?>" />
    <input type="hidden" name="task" value="" />
    <?php echo JHtml::_('form.token'); ?>
</form>

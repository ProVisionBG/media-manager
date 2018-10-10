<?php
/**
 * Copyright (c) 2017. ProVision Media Group Ltd. <http://provision.bg>
 * Venelin Iliev <http://veneliniliev.com>
 */

/*
 * ProVision Administration, http://ProVision.bg
 * Author: Venelin Iliev, http://veneliniliev.com
 */

namespace ProVision\MediaManager\Forms;

use ProVision\Administration\Forms\AdminForm;

class ItemForm extends AdminForm
{
    public function buildForm()
    {
        $this->add('title', 'text', [
            'label' => trans('media-manager::admin.edit-form.title'),
            'translate' => true,

        ]);

        $this->add('description', 'textarea', [
            'label' => trans('media-manager::admin.edit-form.description'),
            'translate' => true,
        ]);

        $this->add('link', 'text', [
            'label' => trans('media-manager::admin.edit-form.link'),
            'translate' => true,
        ]);

        $this->add('visible', 'checkbox', [
            'label' => trans('media-manager::admin.edit-form.visible'),
            'value' => 1,
            'default_checked' => true,
            'default_value' => 1,
            'translate' => true,
            'help_block' => [
                'text' => trans('media-manager::admin.edit-form.visible-help')
            ]
        ]);

        $this->add('footer', 'admin_footer');
    }
}

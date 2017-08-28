<?php

namespace App\Presenters;

use \Nette,
    \Nette\Forms\Controls;


/**
 * Base presenter for all application presenters.
 */
abstract class BasePresenter extends Nette\Application\UI\Presenter
{

    /**
     * @param string
     * @return Nette\ComponentModel\IComponent
     */
    protected function createComponent($name)
    {
        $component = parent::createComponent($name);
        if ($component instanceof Nette\Application\UI\Form) {
            $this->bootstrapize($component);
        }
        return $component;
    }

    /**
     * Bootstrapizes the form.
     * @param Nette\Application\UI\Form
     * @return void
     */
    private function bootstrapize(Nette\Application\UI\Form $form)
    {
        // setup form rendering
        $renderer = $form->getRenderer();
        $renderer->wrappers['controls']['container'] = 'div class="rounded form-container"';
        $renderer->wrappers['pair']['container'] = 'div class=form-group';
        $renderer->wrappers['pair']['.error'] = 'has-error';
        $renderer->wrappers['control']['container'] = NULL;
//        $renderer->wrappers['label']['container'] = 'div class="col-sm-3 control-label"';
        $renderer->wrappers['label']['container'] = NULL;
        $renderer->wrappers['control']['description'] = 'span class=help-block';
//        $renderer->wrappers['control']['description'] = 'span class=help-block';
        $renderer->wrappers['control']['errorcontainer'] = 'div class="alert alert-danger alert-form" role="alert"';
        // make form and controls compatible with Twitter Bootstrap
        $form->getElementPrototype()->class('form-horizontal');
        foreach ($form->getControls() as $control) {
            if ($control instanceof Controls\Button) {
                $control->getControlPrototype()->addClass('btn-block btn-form');
                $control->getControlPrototype()->addClass(empty($usedPrimary) ? 'btn btn-primary' : 'btn btn-default');
                $usedPrimary = TRUE;
            } elseif ($control instanceof Controls\TextBase || $control instanceof Controls\SelectBox || $control instanceof Controls\MultiSelectBox) {
                $control->getControlPrototype()->addClass('form-control');
            } elseif ($control instanceof Controls\Checkbox || $control instanceof Controls\CheckboxList || $control instanceof Controls\RadioList) {
                $control->getSeparatorPrototype()->setName('div')->addClass($control->getControlPrototype()->type);
            }
        }
        return;
    }
}
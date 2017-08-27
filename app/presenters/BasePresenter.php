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
        $renderer->wrappers['pair']['container'] = 'div class="row input-row"';
//        $renderer->wrappers['pair']['container'] = 'div class=input-group';
        $renderer->wrappers['pair']['.error'] = 'has-error';
        $renderer->wrappers['control']['container'] = 'div class=col-sm-7';
//        $renderer->wrappers['control']['container'] = NULL;
        $renderer->wrappers['label']['container'] = 'div class="col-sm-5 control-label"';
//        $renderer->wrappers['label']['container'] = 'span class=input-group-addon control-label"';
        $renderer->wrappers['control']['description'] = 'span class=help-block';
        $renderer->wrappers['control']['errorcontainer'] = 'span class=help-block';
        // make form and controls compatible with Twitter Bootstrap
        $form->getElementPrototype()->class('form-horizontal');
        foreach ($form->getControls() as $control) {
            if ($control instanceof Controls\Button) {
                $control->getControlPrototype()->addClass('btn btn-primary');
                $usedPrimary = TRUE;
            } elseif ($control instanceof Controls\TextBase || $control instanceof Controls\SelectBox || $control instanceof Controls\MultiSelectBox) {
                $control->getControlPrototype()->addClass('form-control');
            } elseif ($control instanceof Controls\Checkbox) {
                $control->getControlPrototype()->addClass('toggle');
                //$control->getSeparatorPrototype()->setName('div')->addClass('checkbox check-transparent');

            } elseif ($control instanceof Controls\CheckboxList || $control instanceof Controls\RadioList) {
                $control->getSeparatorPrototype()->setName('div')->addClass($control->getControlPrototype()->type);
            }
        }
        $form->setRenderer($renderer);
    }
}
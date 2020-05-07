<?php

namespace Sng\Recordsmanager\ViewHelpers;

/*
 * This file is part of the "recordsmanager" Extension for TYPO3 CMS.
 *
 * For the full copyright and license information, please read the
 * LICENSE.txt file that was distributed with this source code.
 */

use TYPO3\CMS\Backend\Form\NodeFactory;
use TYPO3\CMS\Fluid\ViewHelpers\Form\AbstractFormFieldViewHelper;
use TYPO3\CMS\Core\Utility\GeneralUtility;
use TYPO3\CMS\Backend\Form\FormResultCompiler;

/**
 * This view helper generates a <input> with calendar selector and date format control
 *
 * = Basic usage =
 *
 * <code title="Basic usage">
 * <rm:form.date name="xxxx" />
 * </code>
 */
class DateViewHelper extends AbstractFormFieldViewHelper
{

    /**
     * @var string
     */
    protected $tagName = 'input';

    /**
     * Initialize the arguments.
     *
     * @author Sebastian Kurfürst <sebastian@typo3.org>
     * @author Christopher Hlubek <hlubek@networkteam.com>
     * @api
     */
    public function initializeArguments()
    {
        parent::initializeArguments();
        $this->registerUniversalTagAttributes();
        $this->registerTagAttribute('formName', 'string', 'Form name (need for JS calendar)', true);
    }

    /**
     * Render the tag.
     *
     * @return string rendered tag.
     * @api
     */
    public function render()
    {
        $this->tag->addAttribute('name', $this->arguments['name']);
        $this->tag->addAttribute('value', $this->arguments['value']);

        $parameterArray = [
            'itemFormElName' => $this->arguments['name'],
            'itemFormElValue' => $this->arguments['value'],
            'fieldConf' => [
                'config' => [
                    'type' => 'input',
                    'renderType' => 'inputDateTime',
                    'eval' => 'date',
                    'default' => 0
                ]
            ],
        ];

        $options = [
            'renderType' => 'inputDateTime',
            //            'table'          => 'inputDateTime',
            'fieldName' => $this->arguments['name'],
            //            'databaseRow'    => [],
            'parameterArray' => $parameterArray
        ];

        $nodeFactory = GeneralUtility::makeInstance(NodeFactory::class);
        $inputDateTimeResult = $nodeFactory->create($options)->render();
        $formResultCompiler = GeneralUtility::makeInstance(FormResultCompiler::class);
        $formResultCompiler->mergeResult($inputDateTimeResult);
        $formResultCompiler->printNeededJSFunctions();
        return $inputDateTimeResult['html'];
    }
}

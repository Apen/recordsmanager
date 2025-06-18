<?php

declare(strict_types=1);

namespace Sng\Recordsmanager\ViewHelpers;

/*
 * This file is part of the "recordsmanager" Extension for TYPO3 CMS.
 *
 * For the full copyright and license information, please read the
 * LICENSE.txt file that was distributed with this source code.
 */

use Sng\Recordsmanager\Utility\Misc;
use TYPO3\CMS\Backend\Form\FormResultCompiler;
use TYPO3\CMS\Backend\Form\NodeFactory;
use TYPO3\CMS\Core\Utility\GeneralUtility;
use TYPO3\CMS\Fluid\ViewHelpers\Form\AbstractFormFieldViewHelper;

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
     */
    public function initializeArguments(): void
    {
        parent::initializeArguments();
    }

    /**
     * Render the tag.
     *
     * @return string rendered tag.
     */
    public function render(): string
    {
        $this->tag->addAttribute('name', $this->arguments['name']);
        $this->tag->addAttribute('value', $this->arguments['value']);

        // save debug context to not display fieldname
        $debug = $GLOBALS['TYPO3_CONF_VARS']['BE']['debug'];
        $GLOBALS['TYPO3_CONF_VARS']['BE']['debug'] = false;

        $parameterArray = [
            'itemFormElName' => $this->arguments['name'],
            'itemFormElValue' => empty($this->arguments['value']) ? 0 : strtotime($this->arguments['value']),
            'itemFormElID' => 'data_notable_0_' . $this->arguments['name'],
            'fieldConf' => [
                'label' => '',
                'config' => [
                    'type' => 'datetime',
                    'format' => 'date',
                    'default' => 0,
                ],
            ],
        ];
        $options = [
            'renderType' => 'datetime',
            'tableName' => '',
            'fieldName' => $this->arguments['name'],
            'databaseRow' => [
                'uid' => 0,
            ],
            'processedTca' => [
                'columns' => [
                    $this->arguments['name'] => $parameterArray['fieldConf'],
                ],
            ],
            'parameterArray' => $parameterArray,
        ];

        $nodeFactory = GeneralUtility::makeInstance(NodeFactory::class);
        $inputDateTimeResult = $nodeFactory->create($options)->render();
        $formResultCompiler = GeneralUtility::makeInstance(FormResultCompiler::class);
        $formResultCompiler->mergeResult($inputDateTimeResult);
        $formResultCompiler->printNeededJSFunctions();

        $GLOBALS['TYPO3_CONF_VARS']['BE']['debug'] = $debug;

        return $inputDateTimeResult['html'];
    }
}

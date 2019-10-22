<?php

namespace Sng\Recordsmanager\ViewHelpers;

/*
 * This file is part of the "recordsmanager" Extension for TYPO3 CMS.
 *
 * For the full copyright and license information, please read the
 * LICENSE.txt file that was distributed with this source code.
 */

/**
 * This view helper generates a <input> with calendar selector and date format control
 *
 * = Basic usage =
 *
 * <code title="Basic usage">
 * <rm:form.date name="xxxx" />
 * </code>
 *
 * @license http://www.gnu.org/licenses/lgpl.html GNU Lesser General Public License, version 3 or later
 * @api
 */
class DateViewHelper extends \TYPO3\CMS\Fluid\ViewHelpers\Form\AbstractFormFieldViewHelper
{

    /**
     * @var string
     */
    protected $tagName = 'input';

    /**
     * Initialize the arguments.
     *
     * @return void
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
            'itemFormElName'  => $this->arguments['name'],
            'itemFormElValue' => $this->arguments['value'],
            'fieldConf'       => [
                'config' => [
                    'type'       => 'input',
                    'renderType' => 'inputDateTime',
                    'eval'       => 'date',
                    'default'    => 0
                ]
            ],
        ];

        $options = [
            'renderType'     => 'inputDateTime',
            //            'table'          => 'inputDateTime',
            'fieldName'      => $this->arguments['name'],
            //            'databaseRow'    => [],
            'parameterArray' => $parameterArray
        ];

        $nodeFactory = \TYPO3\CMS\Core\Utility\GeneralUtility::makeInstance('TYPO3\\CMS\\Backend\\Form\\NodeFactory');
        $inputDateTimeResult = $nodeFactory->create($options)->render();
        $formResultCompiler = \TYPO3\CMS\Core\Utility\GeneralUtility::makeInstance(\TYPO3\CMS\Backend\Form\FormResultCompiler::class);
        $formResultCompiler->mergeResult($inputDateTimeResult);
        $formResultCompiler->printNeededJSFunctions();
        return $inputDateTimeResult['html'];
    }
}

?>
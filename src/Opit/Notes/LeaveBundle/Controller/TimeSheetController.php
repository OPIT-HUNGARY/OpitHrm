<?php

/*
 * The MIT License
 *
 * Copyright 2014 OPIT\bota.
 *
 * Permission is hereby granted, free of charge, to any person obtaining a copy
 * of this software and associated documentation files (the "Software"), to deal
 * in the Software without restriction, including without limitation the rights
 * to use, copy, modify, merge, publish, distribute, sublicense, and/or sell
 * copies of the Software, and to permit persons to whom the Software is
 * furnished to do so, subject to the following conditions:
 *
 * The above copyright notice and this permission notice shall be included in
 * all copies or substantial portions of the Software.
 *
 * THE SOFTWARE IS PROVIDED "AS IS", WITHOUT WARRANTY OF ANY KIND, EXPRESS OR
 * IMPLIED, INCLUDING BUT NOT LIMID TO THE WARRANTIES OF MERCHANTABILITY,
 * FITNESS FOR A PARTICULAR PURPOSE AND NONINFRINGEMENT. IN NO EVENT SHALL THE
 * AUTHORS OR COPYRIGHT HOLDERS BE LIABLE FOR ANY CLAIM, DAMAGES OR OTHER
 * LIABILITY, WHETHER IN AN ACTION OF CONTRACT, TORT OR OTHERWISE, ARISING FROM,
 * OUT OF OR IN CONNECTION WITH THE SOFTWARE OR THE USE OR OTHER DEALINGS IN
 * THE SOFTWARE.
 */

namespace Opit\Notes\LeaveBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Template;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Method;
use Symfony\Component\HttpFoundation\Request;
use JMS\SecurityExtraBundle\Annotation\Secure;

/**
 * Description of TimeSheetController
 *
 * @author OPIT Consulting Kft. - PHP Team - {@link http://www.opit.hu}
 * @version 1.0
 * @package Notes
 * @subpackage LeaveBundle
 */
class TimeSheetController extends Controller
{
    /**
     * To list time sheets in Notes
     *
     * @Route("/secured/timesheet/list", name="OpitNotesLeaveBundle_timesheet_list")
     * @Secure(roles="ROLE_USER")
     * @Template()
     */
    public function listsTimeSheetAction(Request $request)
    {
        $showList = (boolean) $request->request->get('showList');
        $maxMonth = date('n');
        $availableMonths = array();
        
        if ($showList) {
            ++$maxMonth;
        }
        
        // Generate the pervious months with the numeric and name represantions.
        for ($i = --$maxMonth; $i > 0; $i--) {
            $availableMonths[$i] = date('Y F', mktime(0, 0, 0, $i, 1));
        }
        
        return $this->render(
            'OpitNotesLeaveBundle:TimeSheet:' . ($showList ? '_' : '') . 'listTimeSheet.html.twig',
            array('availableMonths' => $availableMonths)
        );
    }
}

<?php

/**
 * HTML report for PHP_CodeSniffer.
 *
 * PHP version 5
 *
 * @category  PHP
 * @package   PHP_CodeSniffer
 * @author    Don Bauer <lordgnu@me.com>
 * @copyright 2012 BauerBox Labs
 * @license   https://github.com/squizlabs/PHP_CodeSniffer/blob/master/licence.txt BSD Licence
 * @version   Release: @package_version@
 * @link      http://pear.php.net/package/PHP_CodeSniffer
 */
class PHP_CodeSniffer_Reports_Html implements PHP_CodeSniffer_Report
{

    private $logoUrl = 'http://96.43.129.196/pub/revco.png';
    private $bootstrapThemeUrl = 'http://netdna.bootstrapcdn.com/bootswatch/2.1.0/cerulean/bootstrap.min.css';
    private $companyName = 'REV!CO';

    /**
     * Prints all violations for processed files, in a proprietary XML format.
     *
     * Errors and warnings are displayed together, grouped by file.
     *
     * @param array   $report      Prepared report.
     * @param boolean $showSources Show sources?
     * @param int     $width       Maximum allowed lne width.
     * @param boolean $toScreen    Is the report being printed to screen?
     *
     * @return string
     */
    public function generate(
        $report,
        $showSources=false,
        $width=80,
        $toScreen=true
    ) {
        $content = '';
        $html = <<<HTML
<!doctype html>
<html>
    <head>
        <meta charset="utf-8">
        <title>REV!CO Code Sniffer Report</title>
        <meta name="viewport" content="width=device-width, initial-scale=1.0">

        <!-- Bootstrap Hosted Css -->
        <link href="http://netdna.bootstrapcdn.com/twitter-bootstrap/2.2.0/css/bootstrap-combined.min.css" rel="stylesheet">
        <style type="text/css">
            body {
                padding-top: 60px;
                padding-bottom: 40px;
            }
        </style>

        <link href="{$this->bootstrapThemeUrl}" rel="stylesheet">
    </head>
    <body data-spy="scroll" data-target="#phpcs-nav-bar" data-offset="0">
        <div id="phpcs-nav-bar" class="navbar navbar-inverse navbar-fixed-top">
            <div class="navbar-inner">
              <div class="container">
                <a class="btn btn-navbar" data-toggle="collapse" data-target=".nav-collapse">
                  <span class="icon-bar"></span>
                  <span class="icon-bar"></span>
                  <span class="icon-bar"></span>
                </a>
                <a class="brand" href="#"><img src="{$this->logoUrl}" style="height: 16px"/> Standards</a>
                <div class="nav-collapse collapse">
                    <ul class="nav">
                        <li class="active"><a href="#summary">Summary</a></li>
                        <li><a href="#details">Details</a></li>
                    </ul>
                    <!--
                    <ul class="nav pull-right">
                        <li class="dropdown">
                            <a href="#" class="dropdown-toggle" data-toggle="dropdown">Files <b class="caret"></b></a>
                            <ul class="dropdown-menu">

                                <li class="divider"></li>
                                ##_NAV_CONTENT_##
                            </ul>
                        </li>
                    </ul>
                    -->
                </div>
              </div>
            </div>
        </div>

        <div class="container">
                <div class="row page-header">
                    <h1>{$this->companyName} Standards <small>CodeSniffer Report</small></h1>
                </div>
                <div class="row">
                    <a id="summary"></a>
                    <h2>Summary</h2>
                    ##_SUMMARY_CONTENT_##
                </div>
                <div class="row">
                    <a id="details"></a>
                    <h2>Details <small>Only files needing remediation shown</small></h2>
                    ##_CONTENT_##
                </div>
        </div>
        <footer class="footer">
            <div class="container">
            <p class="pull-right"><a href="#">Back to top</a></p>
            <p>Report Generated: ##_DATE_##</p>
            </div>
        </footer>
        <!-- jQuery CDN -->
        <script src="http://ajax.googleapis.com/ajax/libs/jquery/1.8.2/jquery.min.js"></script>

        <!-- Bootstrap Hosted JS -->
        <script src="http://netdna.bootstrapcdn.com/twitter-bootstrap/2.1.0/js/bootstrap.min.js"></script>
    </body>
</html>
HTML;

        $fileTemplate = <<<HTML
<a name="%s"></a>
<div>
    <!--
    <button type="button" class="button button-small pull-right" data-toggle="collapse" data-target="#%s">Detail</button>
    <span class="label label-warning pull-right">%d Warning%s</span>
    <span class="label label-important pull-right">%d Error%s</span>
    -->
    %s
</div>
<div id="%s" class="collapse">
    <table class="table table-bordered table-striped table-hover">
        <thead>
            <tr>
                <th>Type</th>
                <th>Line</th>
                <th>Column</th>
                <th>Source</th>
                <th>Severity</th>
                <th>Message</th>
            </tr>
        </thead>
        <tbody>
            ##_FILE_CONTENT_##
        </tbody>
    </table>
</div>
HTML;
        $summaryTemplate = <<<HTML
    <h3>Overall Score <small>File Based</small></h3>
    <div class="progress">
        <div class="bar bar-success" style="width: %d%%;"></div>
        <div class="bar bar-warning" style="width: %d%%;"></div>
        <div class="bar bar-danger" style="width: %d%%;"></div>
    </div>

    <div class="row-fluid">
        <div class="span4"><div class="progress"><div class="bar bar-success" style="width: 100%%;">%d %% Passed</div></div></div>
        <div class="span4"><div class="progress"><div class="bar bar-warning" style="width: 100%%;">%d %% Warned</div></div></div>
        <div class="span4"><div class="progress"><div class="bar bar-danger" style="width: 100%%;">%d %% Failed</div></div></div>
    </div>

    <h3>File Breakdown <small>Relative to: <i class="text-info">%s</i></small></h3>
    <table class="table table-bordered table-striped table-hover">
        <thead>
            <tr>
                <th>File</th>
                <th>Warnings</th>
                <th>Errors</th>
            </tr>
        </thead>
        <tbody>
            %s
        </tbody>
    </table>
HTML;
        $summaryRowTemplate = <<<HTML
    <tr>
       <th><span class="label label-%s pull-right">%s</span>%s</th>
       <td>%s</td>
       <td>%s</td>
    </tr>
HTML;

        $messageTemplate = <<<HTML
<tr>
    <td><span class="label label-%s">%s</span></td>
    <td>%d</td>
    <td>%d</td>
    <td>%s</td>
    <td>%s</td>
    <td>%s</td>
</tr>
HTML;

        $navTemplate = <<<HTML
<ul id="nav-bar" class="nav nav-list">
  <li class="nav-header">%s</li>
  <li class="divider"></li>
  <li class="nav-header">Files List</li>
  %s
</ul>
HTML;

        $slugFind = array('/', '\\', '.');
        $slugReplace = array('-', '-', '_');
        $navFiles = array();
        $summaryData = '';

        $errorsShown = 0;

        // Get Top-Most Directory
        $dirName = '';
        $dirNameUnique = false;
        $reset = true;

        while ($dirNameUnique == false) {
            $continue = false;
            foreach ($report['files'] as $filename => $file) {
                if ($reset == true) {
                    $dirName = ($dirName == '') ? dirname($filename) : dirname($dirName);
                    $reset = false;
                }

                if ($continue == true) {
                    continue;
                }

                if (stristr($filename, $dirName) === false) {
                    $continue = true;
                    continue;
                }
            }

            if ($continue == false) {
                $dirNameUnique = true;
            }

            $reset = true;
        }

        $passCount = 0;
        $warnCount = 0;
        $failCount = 0;

        $tempStack = array();
        foreach ($report['files'] as $filename => $file) {
            if ($file['errors'] < 1) {
                $tempStack[$filename] = $file;
                unset($report['files'][$filename]);
            }
        }

        $report['files'] = array_merge($report['files'], $tempStack);

        // Perform Loop
        foreach ($report['files'] as $filename => $file) {

            if ($file['errors'] > 0) {
                $class = 'important';
                ++$failCount;
            } elseif ($file['warnings'] > 0) {
                $class = 'warning';
                ++$warnCount;
            } else {
                $class = 'success';
                ++$passCount;
            }

            $summaryData .= sprintf($summaryRowTemplate,
                $class,
                ($class == 'success' ? 'Pass' : ($class == 'warning' ? 'Warn' : 'Fail')),
                $filename,
                ($file['warnings'] == 0) ? '' : sprintf('<span class="label label-warning" style="display: block; text-align: center">%d</span>', $file['warnings']),
                ($file['errors'] == 0) ? '' : sprintf('<span class="label label-important" style="display: block; text-align: center">%d</span>', $file['errors'])
            );

            if (empty($file['messages']) === true) {
                continue;
            }

            $file['slug'] = str_replace($slugFind, $slugReplace, ltrim($filename, implode('', $slugFind)));

            $navFiles[] = array(
                'slug'  =>  $file['slug'],
                'file'  =>  $filename
            );

            $content .= sprintf($fileTemplate,
                $file['slug'],
                $file['slug'],
                $file['warnings'],
                ($file['warnings'] == 1 ? '' : 's'),
                $file['errors'],
                ($file['errors'] == 1 ? '' : 's'),
                $this->_breadcrumbs(str_replace($dirName, '', $filename), $file['slug'], $file['warnings'], $file['errors']),
                $file['slug']
            );

            $messages = '';

            foreach ($file['messages'] as $line => $lineErrors) {
                foreach ($lineErrors as $column => $colErrors) {
                    foreach ($colErrors as $error) {
                        if ($error['type'] != 'ERROR') {
                            continue;
                        }

                        $error['class'] = ($error['type'] == 'ERROR' ? 'important' : 'warning');

                        $messages .= sprintf($messageTemplate,
                            $error['class'],
                            $error['type'],
                            $line,
                            $column,
                            $error['source'],
                            $error['severity'],
                            $error['message']
                        );

                        $errorsShown++;
                    }

                    foreach ($colErrors as $error) {
                        if ($error['type'] == 'ERROR') {
                            continue;
                        }

                        $error['class'] = ($error['type'] == 'ERROR' ? 'important' : 'warning');

                        $messages .= sprintf($messageTemplate,
                            $error['class'],
                            $error['type'],
                            $line,
                            $column,
                            $error['source'],
                            $error['severity'],
                            $error['message']
                        );

                        $errorsShown++;
                    }
                }
            }//end foreach
            $content = str_replace('##_FILE_CONTENT_##', $messages, $content);
        }//end foreach

        // Caluculate Summary Totals
        $total = $passCount + $failCount + $warnCount;
        $passCount = round(($passCount / $total) * 100, 0, PHP_ROUND_HALF_UP);
        $failCount = round(($failCount / $total) * 100, 0, PHP_ROUND_HALF_UP);
        $warnCount = round(($warnCount / $total) * 100, 0, PHP_ROUND_HALF_UP);

        // Check that these all equal 100 added together
        if (($passCount + $failCount + $warnCount) == 99) {
            if ($failCount == 0) {
                ++$warnCount;
            } else {
                ++$errorCount;
            }
        } elseif (($passCount + $failCount + $warnCount) == 101) {
            if ($passCount > 1) {
                --$passCount;
            } elseif ($warnCount > 1) {
                --$warnCount;
            } else {
                --$errorCount;
            }
        }

        // Build Nav
        $navContent = '';

        foreach ($navFiles as $nav) {
            $nav['file'] = str_replace($dirName, '', $nav['file']);
            $navContent .= "<li><a href=\"#{$nav['slug']}\">{$nav['file']}</a></li>";
        }

        echo str_replace(
            array(
                '##_CONTENT_##',
                '##_NAV_CONTENT_##',
                '##_SUMMARY_CONTENT_##',
                '##_DATE_##'
            ),
            array(
                $content,
                sprintf($navTemplate, $dirName, $navContent),
                sprintf($summaryTemplate, $passCount, $warnCount, $failCount, $passCount, $warnCount, $failCount, $dirName, str_replace($dirName, '', $summaryData)),
                date('r')
            ),
            $html
        );

        return $errorsShown;

    }//end generate()

    private function _breadcrumbs($filename, $slug, $warnings = 0, $errors = 0) {
        //$filename = ltrim($filename, DIRECTORY_SEPARATOR);

        $basename = basename($filename);
        $trail = array();

        $temp = dirname($filename);

        while ($temp != '' && $temp != '/' && $temp != false) {
            array_unshift($trail, $temp);
            $temp = dirname($temp);
        }

        foreach ($trail as $i => &$v) {
            $temp = basename($v);
            if ($temp != '') {
                $v = $temp;
            }
        }

        $out = '<ul class="breadcrumb">';

        foreach ($trail as $crumb) {
            $out .= '<li>' . $crumb . ' <span class="divider">/</span></li>';
        }

        $out .= '<li class="active"><button type="button" class="btn btn-small btn-primary" data-toggle="collapse" data-target="#'.$slug.'" value="'.$basename.'">' . $basename . '</ button></li>';


        $out .= sprintf('<li class="pull-right"><span class="label label-important">Errors: %d</span> </li>', $errors);
        $out .= sprintf('<li class="pull-right"><span class="label label-warning">Warnings: %d</span> <span class="divider">/</span></li>', $warnings);

        $out .= '</ul>';

        return $out;
    }


}//end class

?>
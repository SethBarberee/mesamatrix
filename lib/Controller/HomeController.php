<?php
/*
 * This file is part of mesamatrix.
 *
 * Copyright (C) 2014-2017 Romain "Creak" Failliot.
 *
 * This program is free software: you can redistribute it and/or modify
 * it under the terms of the GNU Affero General Public License as
 * published by the Free Software Foundation, either version 3 of the
 * License, or (at your option) any later version.
 *
 * This program is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 * GNU Affero General Public License for more details.
 *
 * You should have received a copy of the GNU Affero General Public License
 * along with this program.  If not, see <http://www.gnu.org/licenses/>.
 */

namespace Mesamatrix\Controller;

class HomeController extends BaseController
{
    private $commits = array();
    private $leaderboards = array();

    public function __construct() {
        parent::__construct();

        $this->setPage('Home');
    }

    protected function computeRendering() {
        $xml = $this->loadMesamatrixXml();

        $this->createCommitsModel($xml);

        $apis = [ 'OpenGL', 'OpenGL ES', \Mesamatrix\Parser\Constants::GL_OR_ES_EXTRA_NAME ];
        $this->leaderboards['OpenGL'] = $this->createLeaderboard($xml, $apis);

        $apis = [ 'Vulkan', \Mesamatrix\Parser\Constants::VK_EXTRA_NAME ];
        $this->leaderboards['Vulkan'] = $this->createLeaderboard($xml, $apis);
    }

    private function loadMesamatrixXml() {
        $gl3Path = \Mesamatrix::path(\Mesamatrix::$config->getValue('info', 'xml_file'));
        $xml = simplexml_load_file($gl3Path);
        if (!$xml) {
            \Mesamatrix::$logger->critical('Can\'t read '.$gl3Path);
            exit();
        }

        return $xml;
    }

    private function createCommitsModel(\SimpleXMLElement $xml) {
        $this->commits = array();

        $numCommits = \Mesamatrix::$config->getValue('info', 'commitlog_length', 10);
        $numCommits = min($numCommits, $xml->commits->commit->count());
        for ($i = 0; $i < $numCommits; ++$i) {
            $xmlCommit = $xml->commits->commit[$i];
            $this->commits[] = array(
                'url' => \Mesamatrix::$config->getValue('git', 'mesa_web').'/commit/'.\Mesamatrix::$config->getValue('git', 'gl3').'?id='.$xmlCommit['hash'],
                'timestamp' => (int) $xmlCommit['timestamp'],
                'subject' => $xmlCommit['subject']
            );
        }
    }

    private function createLeaderboard(\SimpleXMLElement $xml, array $apis) {
        $leaderboard = new \Mesamatrix\Leaderboard();
        $leaderboard->load($xml, $apis);
        return $leaderboard;
    }

    protected function writeHtmlPage() {
?>
            <div class="stats-commits">
                <h1>Last commits</h1>
                <table class="commits">
                    <thead>
                        <tr>
                            <th>Age</th>
                            <th>Commit message</th>
                        </tr>
                    </thead>
                    <tbody>
<?php
// Commit list.
foreach ($this->commits as $commit):
?>
                        <tr>
                            <td class="commitsAge toRelativeDate" data-timestamp="<?= date(DATE_RFC2822, $commit['timestamp']) ?>"><?= date('Y-m-d H:i', $commit['timestamp']) ?></td>
                            <td><a href="<?= $commit['url'] ?>"><?= $commit['subject'] ?></a></td>
                        </tr>
<?php
endforeach;
?>
                        <tr>
                            <td colspan="2">
                                <noscript>(Dates are UTC)<br/></noscript>
                                <a href="<?= \Mesamatrix::$config->getValue("git", "mesa_web")."/log/docs/features.txt" ?>">More...</a>
                            </td>
                        </tr>
                    </tbody>
                </table>
            </div>

            <h1>Leaderboards</h1>
<?php
foreach ($this->leaderboards as $api => $leaderboard):
    $driversExtsDone = $leaderboard->getDriversSortedByExtsDone();
    $numTotalExts = $leaderboard->getNumTotalExts();
?>
            <div class="stats">
                <div class="stats-lb">
                <h2><?= $api ?></h2>
                    <p>There is a total of <strong><?= $numTotalExts ?></strong> extensions to implement.
                    The ranking is based on the number of extensions done by driver. </p>
                    <table class="lb">
                        <thead>
                            <tr>
                                <th>#</th>
                                <th>Driver</th>
                                <th>Extensions</th>
                                <th>OpenGL</th>
                                <th>OpenGL ES</th>
                            </tr>
                        </thead>
                        <tbody>
<?php
    $index = 1;
    $rank = 1;
    $prevNumExtsDone = -1;
    foreach($driversExtsDone as $drivername => $numExtsDone) {
        $sameRank = $prevNumExtsDone === $numExtsDone;
        if (!$sameRank) {
            $rank = $index;
        }
        switch ($rank) {
        case 1: $rankClass = "lbCol-1st"; break;
        case 2: $rankClass = "lbCol-2nd"; break;
        case 3: $rankClass = "lbCol-3rd"; break;
        default: $rankClass = "";
        }
        $pctScore = sprintf("%.1f%%", $numExtsDone / $numTotalExts * 100);
        $openglVersion = $leaderboard->getDriverGlVersion($drivername);
        if ($openglVersion === NULL) {
            $openglVersion = "N/A";
        }
        $openglesVersion = $leaderboard->getDriverGlesVersion($drivername);
        if ($openglesVersion === NULL) {
            $openglesVersion = "N/A";
        }
?>
                            <tr class="<?= $rankClass ?>">
                                <th class="lbCol-rank"><?= !$sameRank ? $rank : "" ?></th>
                                <td class="lbCol-driver"><?= $drivername ?></td>
                                <td class="lbCol-score"><span class="lbCol-pctScore">(<?= $pctScore ?>)</span> <?= $numExtsDone ?></td>
                                <td class="lbCol-version"><?= $openglVersion ?></td>
                                <td class="lbCol-version"><?= $openglesVersion ?></td>
                            </tr>
<?php
        $prevNumExtsDone = $numExtsDone;
        $index++;
    }
?>
                        </tbody>
                    </table>
                </div>
            </div>
<?php
endforeach;
    }
};

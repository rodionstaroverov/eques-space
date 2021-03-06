<?php
/**
 * Copyright © 2013-2017 Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Test\Legacy;

/**
 * Temporary test that will be removed in scope of MAGETWO-28356.
 * Test verifies obsolete usages in modules that were refactored to work with ResultInterface.
 */
class ObsoleteResponseTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var array
     */
    protected $obsoleteMethods = [];

    /**
     * @var array
     */
    protected $filesBlackList = [];

    /**
     * @var string
     */
    protected $appPath;

    protected function setUp()
    {
        $this->appPath = \Magento\Framework\App\Utility\Files::init()->getPathToSource();
        $this->obsoleteMethods = include __DIR__ . '/_files/response/obsolete_response_methods.php';
        $this->filesBlackList = $this->getBlackList();
    }

    /**
     * Test verify that obsolete methods do not appear in refactored folders
     */
    public function testObsoleteResponseMethods()
    {
        $invoker = new \Magento\Framework\App\Utility\AggregateInvoker($this);
        $invoker(
            function ($file) {
                $content = file_get_contents($file);
                foreach ($this->obsoleteMethods as $method) {
                    $quotedMethod = preg_quote($method, '/');
                    $this->assertSame(
                        0,
                        preg_match('/(?<=[a-z\\d_:]|->|function\\s)' . $quotedMethod . '\\s*\\(/iS', $content),
                        "File: $file\nContains obsolete method: $method . "
                    );
                }
            },
            $this->modulesFilesDataProvider()
        );
    }

    /**
     * Return refactored files
     *
     * @return array
     */
    public function modulesFilesDataProvider()
    {
        $filesList = [];

        foreach ($this->getFilesData('whitelist/refactored_modules*') as $refactoredFolder) {
            $files = \Magento\Framework\App\Utility\Files::init()->getFiles(
                [$this->appPath . $refactoredFolder],
                '*.php'
            );
            $filesList = array_merge($filesList, $files);
        }

        $result = array_map('realpath', $filesList);
        $result = array_diff($result, $this->filesBlackList);
        return \Magento\Framework\App\Utility\Files::composeDataSets($result);
    }

    /**
     * @return array
     */
    protected function getBlackList()
    {
        $blackListFiles = [];
        foreach ($this->getFilesData('blacklist/files_list*') as $file) {
            $blackListFiles[] = realpath($this->appPath . $file);
        }
        return $blackListFiles;
    }

    /**
     * @param string $filePattern
     * @return array
     */
    protected function getFilesData($filePattern)
    {
        $result = [];
        foreach (glob(__DIR__ . '/_files/response/' . $filePattern) as $file) {
            $fileData = include $file;
            $result = array_merge($result, $fileData);
        }
        return $result;
    }
}

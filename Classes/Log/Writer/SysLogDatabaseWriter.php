<?php


namespace EMAILOBFUSCATOR\Emailobfuscator\Log\Writer;


use TYPO3\CMS\Core\Database\ConnectionPool;
use TYPO3\CMS\Core\Log\LogRecord;
use TYPO3\CMS\Core\Log\Writer\DatabaseWriter;
use TYPO3\CMS\Core\Utility\GeneralUtility;

class SysLogDatabaseWriter extends DatabaseWriter
{
    /**
     * Writes the log record
     *
     * @param LogRecord $record Log record
     * @return \TYPO3\CMS\Core\Log\Writer\WriterInterface $this
     */
    public function writeLog(LogRecord $record)
    {
        $data = '';
        $recordData = $record->getData();
        if (!empty($recordData)) {
            // According to PSR3 the exception-key may hold an \Exception
            // Since json_encode() does not encode an exception, we run the _toString() here
            if (isset($recordData['exception']) && $recordData['exception'] instanceof \Exception) {
                $recordData['exception'] = (string)$recordData['exception'];
            }
            $data = '- ' . json_encode($recordData);
        }

        $fieldValues = [
            'request_id' => $record->getRequestId(),
            'time_micro' => $record->getCreated(),
            'component' => $record->getComponent(),
            'level' => $record->getLevel(),
            'message' => $record->getMessage(),
            'data' => $data,
//            'userid' => 1,
            'action' => 3, // dont know
            'type' => 4, // 4 EXTENSION
            'error' => 1, // 1 warning w/ delete, 2 erro w/ delete , 3 error w/o delete
            'details' => $record->getMessage(), // visible text
            'tstamp' => time() // required to show up in backend syslog module
        ];

        GeneralUtility::makeInstance(ConnectionPool::class)
            ->getConnectionForTable($this->logTable)
            ->insert($this->logTable, $fieldValues);

        return $this;
    }
}

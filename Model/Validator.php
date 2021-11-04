<?php
/**
 * This file is part of Zepgram\JsonSchema\Model
 *
 * @package    Zepgram\JsonSchema\Model
 * @file       Validator.php
 * @date       04 11 2021 22:17
 *
 * @author     Benjamin Calef <zepgram@gmail.com>
 * @copyright  2021 Zepgram Copyright (c) (https://github.com/zepgram)
 * @license    MIT License
 **/

declare(strict_types=1);

namespace Zepgram\JsonSchema\Model;

use JsonSchema\Constraints\Constraint;
use JsonSchema\Validator as ValidatorLib;
use Magento\Framework\Exception\FileSystemException;
use Magento\Framework\Filesystem\Driver\File;
use Magento\Framework\Module\Dir;
use Magento\Framework\Module\Dir\Reader;
use Magento\Framework\Serialize\SerializerInterface;
use Zepgram\JsonSchema\Exception\LogicException;
use Zepgram\JsonSchema\Exception\SchemaException;

class Validator
{
    /** @var ValidatorLib */
    private $validator;

    /** @var Reader */
    private $reader;

    /** @var File */
    private $driverFile;

    /** @var SerializerInterface */
    private $jsonSerializer;

    /** @var string */
    private $fileName;

    /** @var string */
    private $moduleName;

    public function __construct(
        ValidatorLib $validator,
        Reader $reader,
        File $driverFile,
        SerializerInterface $jsonSerializer,
        string $fileName = null,
        string $moduleName = null
    ) {
        $this->validator = $validator;
        $this->reader = $reader;
        $this->driverFile = $driverFile;
        $this->jsonSerializer = $jsonSerializer;
        $this->fileName = $fileName;
        $this->moduleName = $moduleName;
    }

    /**
     * @param string $data
     * @return bool
     * @throws FileSystemException
     * @throws LogicException
     * @throws SchemaException
     */
    public function validate(string $data): bool
    {
        $errors = [];
        $data = $this->jsonSerializer->unserialize($data);
        $data = $this->arrayFilterEmptyValues($data);

        $file = $this->getFile();
        $this->driverFile->isExists($file);
        $this->validator->validate(
            $data,
            (object) ['$ref' => 'file://' . $file],
            Constraint::CHECK_MODE_TYPE_CAST
        );

        if (!$this->validator->isValid()) {
            foreach ($this->validator->getErrors() as $error) {
                $errors[] = sprintf('Property [%s]: %s', $error['property'], $error['message']);
            }
            $this->validator->reset();
        }

        if (!empty($errors)) {
            throw new SchemaException(__($this->jsonSerializer->serialize($errors)));
        }

        return true;
    }

    /**
     * @return string
     * @throws LogicException
     */
    private function getFile(): string
    {
        if (empty($this->moduleName)) {
            throw new LogicException(__('Module name must be injected in di.xml'));
        }
        if (empty($this->fileName)) {
            throw new LogicException(__('File name must be injected in di.xml'));
        }
        $moduleDir = $this->reader->getModuleDir(Dir::MODULE_ETC_DIR, $this->moduleName);

        return $moduleDir . DIRECTORY_SEPARATOR . $this->fileName;
    }

    /**
     * @param array $input
     * @return array
     */
    private function arrayFilterEmptyValues(array $input): array
    {
        foreach ($input as &$value) {
            if (is_array($value)) {
                $value = $this->arrayFilterEmptyValues($value);
            }
        }

        return array_filter($input, function ($item) {
            return $item !== null;
        });
    }
}

<?php

namespace utils;

use api\Response;

class FileManager
{

    private Response $response;

    public function __construct(Response $response)
    {
        $this->response = $response;
    }

    private function validateDocumentExtension(array $alloweds_ext): string
    {
        $ext = strtolower(pathinfo($_FILES['files']['name'][0], PATHINFO_EXTENSION));
        if (!in_array($ext, $alloweds_ext)) {
            $imploded = implode(',', $alloweds_ext);
            $this->response->printError(
                "The file in your request does not have a valid extension valid are: $imploded",
                400);
        }
        return $ext;
    }

    /**
     *
     * @return int
     */
    public function filesCount(): int
    {
        if (!isset($_FILES['files'])) {
            return 0;
        }
        if (gettype($_FILES['files']['tmp_name']) !== "array") {
            return 0;
        }
        return count($_FILES['files']['tmp_name']);
    }


    public function saveAllDocuments($path): array
    {
        $files = [];
        foreach ($_FILES['files']['tmp_name'] as $file) {
            if (!is_uploaded_file($file)) {
                $this->response->printError('File has not been successfully uploaded', 500);
            }
        }
        for ($i = 0; $i < count($_FILES['files']['tmp_name']); $i++) {
            $file = $_FILES['files']['tmp_name'][$i];
            $ext = strtolower(pathinfo($_FILES['files']['name'][$i], PATHINFO_EXTENSION));
            $uuid = Functions::guidv4();
            $final_path = "$path/$uuid.$ext";
            $files[] = [
                'path' => $final_path,
                'name' => $uuid,
                'ext' => $ext,
                'uuid' => Functions::guidv4()
            ];
            move_uploaded_file($file, $final_path);
        }
        return $files;
    }

    public function isValidExtension(array $allowed_extensions): bool
    {
        if ($this->filesCount() === 0) {
            return false;
        }
        foreach ($_FILES['files']['name'] as $file) {
            $ext = strtolower(pathinfo($file, PATHINFO_EXTENSION));
            if (!in_array($ext, $allowed_extensions)) {
                return false;
            }
        }
        return true;
    }


}


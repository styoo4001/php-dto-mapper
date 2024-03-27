<?php

namespace Styoo4001\PhpDtoMapper;

use Illuminate\Http\Request;

/** Class CommandObjectMapper */
class CommandObjectMapper
{
    public static function mapping(RequestCommand $commandObject, array $mappingData)
    {
        $mapper = new DataTransferObjectMapper();
        $validator = new SomeValidator();  // implements CommandObjectValidator
        $validator->setData($mappingData);
        $commandObject->validation($validator);

        if (! $commandObject->hasErrors()) {
            $mapper->mapping(array_merge($mappingData, $validator->getValidatedData()), $commandObject);
        }

        // mapping 에러가 있다면 commandObject 에러과 합침
        if ($mapper->hasErrors()) {
            $commandObject->setErrors(...$mapper->getErrors());
        }

        // commandObject의 validator를 설정한다.
        return $commandObject;
    }

    public static function getMappingData(Request $request)
    {
        $content = $request->getContent();
        //request 내용이 json content면 json_decode 한 후에 mapping.
        if (! empty($request) && $request->isJson()) {
            $jsonData = @json_decode($content, true);

            if (json_last_error() === JSON_ERROR_NONE) {
                return $jsonData;
            } else {
                parse_str($content, $mappingData);

                return $mappingData;
            }
            // json요청이 아니면 $request->all() 로 받아온 데이터 전체를 mapping 시킨다.
        }

        return $request->all();
    }
}

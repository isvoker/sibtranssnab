<?php
/* controller "specific" */
try {
	//DBCommand::begin();

	switch ($action) {
		case 'callback':
            $fields = Request::getVar('fields', 'string');

            if (!$fields) {
                break;
            }

            $isSuccess = FormCallbackSender::sendCustomNotice(
                getFormDataFromJson($fields, false)
            );

            if ($isSuccess) {
                $data = Ajax::getDataOk();
                $data['msg'] = 'Спасибо! Ваш запрос поступил в обработку.';
            } else {
                $data = [
                    'status' => Response::STATUS_UNKNOWN_ERROR,
                    'error' => 'При отправке сообщения произошла ошибка.'
                ];
            }

			break;

        case 'callback_on_banner':
            $fields = Request::getVar('fields', 'string');

            if (!$fields) {
                break;
            }

            $isSuccess = FormCallbackOnBannerSender::sendCustomNotice(
                getFormDataFromJson($fields, false)
            );

            if ($isSuccess) {
                $data = Ajax::getDataOk();
                $data['msg'] = 'Сообщение успешно отправлено.';
            } else {
                $data = [
                    'status' => Response::STATUS_UNKNOWN_ERROR,
                    'error' => 'При отправке сообщения произошла ошибка.'
                ];
            }

            break;

		default:
			break;
	}

	//DBCommand::commit();
} catch (Throwable $E) {
	//DBCommand::rollback();
	$data = Ajax::getDataError($E);
}

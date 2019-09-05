<?php defined('BASEPATH') || exit('No direct script access allowed');

class Api extends CommonBase
{
    public function __construct()
    {
        parent::__construct();
    }

    /**
     * @OA\Post(
     *   path="/api/websiteClose",
     *   summary="網站維護資訊",
     *   tags={"Api"},
     *   @OA\RequestBody(
     *       @OA\MediaType(
     *           mediaType="application/x-www-form-urlencoded",
     *           @OA\Schema(
     *               type="object",
     *               @OA\Property(
     *                   property="source",
     *                   description="來源 wap,pc,android,ios",
     *                   type="string",
     *                   example="wap",
     *                   enum={"wap","pc","android","ios"}
     *               ),
     *               required={"source"}
     *           )
     *       )
     *   ),
     *   @OA\Response(response="200", description="Success")
     * )
     */
    public function websiteClose()
    {
        $this->output->set_content_type('application/json')->set_output(json_encode([
            'status'  => 1,
            'code'    => 200,
            'message' => "success",
            'data'    => [
                'message' => $this->site_config['website_close_message'],
                'picture' => $this->site_config["website_close_picture_wap"],
            ],
        ]));
    }
}

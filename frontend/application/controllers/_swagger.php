<?php
/**
 * @OA\Info(
 *   title="彩票API",
 *   version="1.2"
 * )
 */

/**
 * @OA\Server(
 *   url="{schema}://lotteryapi",
 *   description="本機開發",
 *   @OA\ServerVariable(
 *       serverVariable="schema",
 *       enum={"https", "http"},
 *       default="http"
 *   )
 * )
 */

/**
 * @OA\Server(
 *   url="{schema}://wap.tlmaster.com",
 *   description="測試機",
 *   @OA\ServerVariable(
 *       serverVariable="schema",
 *       enum={"https", "http"},
 *       default="http"
 *   )
 * )
 */

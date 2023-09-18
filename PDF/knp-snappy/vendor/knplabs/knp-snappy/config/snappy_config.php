<?php

/**
 * Generic name for a blank image.
 */
define ('K_BLANK_IMAGE', '_blank.png');

/**
 * Page format.
 */
define ('KNP_PDF_PAGE_FORMAT', 'A4');

/**
 * Page orientation (P=portrait, L=landscape).
 */
define ('KNP_PDF_PAGE_ORIENTATION', 'P');

/**
 * Document creator.
 */
define ('KNP_PDF_CREATOR', 'SNAPPY');

/**
 * Document author.
 */
define ('KNP_PDF_AUTHOR', 'SNAPPY');

/**
 * Header title.
 */
define ('KNP_PDF_HEADER_TITLE', 'SNAPPY Example');

/**
 * Header description string.
 */
define ('KNP_PDF_HEADER_STRING', "");

/**
 * Document unit of measure [pt=point, mm=millimeter, cm=centimeter, in=inch].
 */
define ('KNP_PDF_UNIT', 'mm');

/**
 * Header margin.
 */
define ('KNP_PDF_MARGIN_HEADER', 5);

/**
 * Footer margin.
 */
define ('KNP_PDF_MARGIN_FOOTER', 10);

/**
 * Top margin.
 */
define ('KNP_PDF_MARGIN_TOP', 10);

/**
 * Bottom margin.
 */
define ('KNP_PDF_MARGIN_BOTTOM', 25);

/**
 * Left margin.
 */
define ('KNP_PDF_MARGIN_LEFT', 10);

/**
 * Right margin.
 */
define ('KNP_PDF_MARGIN_RIGHT', 10);

/**
 * Default main font name.
 */
define ('KNP_PDF_FONT_NAME_MAIN', 'helvetica');

/**
 * Default main font size.
 */
define ('KNP_PDF_FONT_SIZE_MAIN', 10);

/**
 * Default data font name.
 */
define ('KNP_PDF_FONT_NAME_DATA', 'helvetica');

/**
 * Default data font size.
 */
define ('KNP_PDF_FONT_SIZE_DATA', 7);

/**
 * Default monospaced font name.
 */
define ('KNP_PDF_FONT_MONOSPACED', 'courier');

/**
 * Ratio used to adjust the conversion of pixels to user units.
 */
define ('KNP_PDF_IMAGE_SCALE_RATIO', 1.25);

/**
 * Magnification factor for titles.
 */
define('HEAD_MAGNIFICATION', 1.1);

/**
 * Height of cell respect font height.
 */
define('K_CELL_HEIGHT_RATIO', 1.25);

/**
 * Title magnification respect main font size.
 */
define('K_TITLE_MAGNIFICATION', 1.3);

/**
 * Reduction factor for small font.
 */
define('K_SMALL_RATIO', 2/3);

/**
 * Set to true to enable the special procedure used to avoid the overlappind of symbols on Thai language.
 */
define('K_THAI_TOPCHARS', true);

/**
 * If true allows to call SNAPPY methods using HTML syntax
 * IMPORTANT: For security reason, disable this feature if you are printing user HTML content.
 */
define('K_SNAPPY_CALLS_IN_HTML', false);

/**
 * If true and PHP version is greater than 5, then the Error() method throw new exception instead of terminating the execution.
 */
define('K_SNAPPY_THROW_EXCEPTION_ERROR', false);

/**
 * Default timezone for datetime functions
 */
define('K_TIMEZONE', 'UTC');

//============================================================+
// END OF FILE
//============================================================+

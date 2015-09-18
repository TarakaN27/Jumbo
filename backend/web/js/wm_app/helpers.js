/**
 * Webmart Soft
 * Created by zhenya on 18.09.15.
 * Useful scripts
 * Only native JavaScripts!!!!!
 */
/**
 * Get number from sting
 * @param str
 * @returns {Number}
 */
function parseNum(str){ return parseFloat(String(str).match(/\d+(?:\.\d+)?/g, '') || 0, 10); }
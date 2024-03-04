/**
 * Renders an img tag as an SVG if src attribute is an svg.
 * 
 * @since 0.1.0
 * 
 * @param {HTMLImageElement} imgElement 
 */
async function renderInlineSvg(imgElement) {
  // Check if the image element has an SVG URL
  if (!imgElement.src.endsWith('.svg')) {
    return; // Not an SVG image, do nothing
  }

  const response = await fetch(imgElement.src);
  const svgContent = await response.text();

  // Parse the SVG
  const doc = new DOMParser().parseFromString(svgContent, 'image/svg+xml');
  const svgElement = doc.firstChild;

  // Remove SVG height/width attributes and inherit
  svgElement.removeAttribute('height');
  svgElement.removeAttribute('width');

  // Duplicate atributes from img to SVG
  const attributes = imgElement.attributes;
  for (let i = 0; i < attributes.length; i++) {
    const attr = attributes[i];
    if (attr.name !== 'src') {
      svgElement.setAttribute(attr.name, attr.value);
    }
  }

  // Replace the image element with the SVG element
  imgElement.parentElement.replaceChild(svgElement, imgElement);
}

/**
 * Check for eligible image tag elements for inlining.
 * 
 * @since 0.1.0
 */
function checkSvgs() {
  // Select image tag from photo modules with setting enabled
  const imgElements = document.querySelectorAll('[data-svg-inline="true"] img');

  // Loop through each img element
  imgElements.forEach(renderInlineSvg);
}

// Init
document.addEventListener('DOMContentLoaded',function() {

  checkSvgs();

  jQuery( '.fl-builder-content' ).on( 'fl-builder.layout-rendered', function() {
    checkSvgs();
  });

  jQuery( '.fl-builder-content' ).on( 'fl-builder.preview-rendered', function() {
    checkSvgs();
  });
});

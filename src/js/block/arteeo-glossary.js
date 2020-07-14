const BLOCK_CLASS = `wp-block-arteeo-glossary-block`;

/**
 * Render glossary if necessary.
 */
import Glossary from './frontend-glossary';

document.addEventListener( 'DOMContentLoaded', () => {
	const wrappers = document.getElementsByClassName(
		BLOCK_CLASS
	);

	for ( const wrapper of wrappers ) {
		let secondaryColor = wrapper.dataset.secondaryColor;
		let primaryColor   = wrapper.dataset.primaryColor;
		let name           = wrapper.dataset.name;
		let locale         = wrapper.dataset.locale;
		let __selectLetter = wrapper.dataset.__selectLetter;
		if ( ! wrapper.classList.contains('edit') ) {
			let gloss = new Glossary(wrapper, primaryColor, secondaryColor, __selectLetter, locale);
		}
	}
} );

/**
 * Enable auto resizing inside container
 */
let animationFrameID = null;
const containerWidth = [];

function animationFrameLoop() {
	const containers = document.querySelectorAll(
		'.' + BLOCK_CLASS
	);

	containers.forEach( function ( currentValue, currentIndex, listObj ) {
		const newContainerWidth = currentValue.offsetWidth;

		if ( newContainerWidth !== containerWidth[ currentIndex ] ) {
			handleContainerWidthChanged( currentValue, newContainerWidth );
			containerWidth[ currentIndex ] = newContainerWidth;
		}
	}, '' );

	animationFrameID = window.requestAnimationFrame( animationFrameLoop );
}

function handleContainerWidthChanged( container, newContainerWidth ) {
	container.classList.toggle( 'sm', newContainerWidth >= 576 );
	container.classList.toggle( 'md', newContainerWidth >= 768 );
	container.classList.toggle( 'lg', newContainerWidth >= 992 );
	container.classList.toggle( 'xl', newContainerWidth >= 1200 );
}

document.addEventListener( 'DOMContentLoaded', () => {
	animationFrameLoop();
} );
(function ()
{
	function isVisible(iframe)
	{
		var rect   = iframe.getBoundingClientRect(),
			top    = -50,
			bottom = innerHeight + 100;

		return (rect.bottom > top && rect.top < bottom && rect.width);
	}

	function setFlag()
	{
		scrolling = true;
	}

	function collectIframes()
	{
		var iframes = document.getElementsByTagName('iframe'),
			cnt = iframes.length,
			i = -1;
		while (++i < cnt)
		{
			var iframe = iframes[i];
			if (canBeLazyLoaded(iframe))
			{
				lazyIframes.push(iframe);
				var onload = iframe.getAttribute('onload');
				if (iframe.hasAttribute('onload') && onload.indexOf('data-lazy') < 0)
				{
					iframe.setAttribute('onload', "if(!hasAttribute('data-lazy')){" + onload + '}');
				}
				iframe.setAttribute('data-lazy', '');
				iframe.contentWindow.location.replace('data:text/html,');
			}
		}
	}

	function canBeLazyLoaded(iframe)
	{
		return (!iframe.hasAttribute('data-lazy') && (iframe.hasAttribute('data-s9e-mediaembed') || iframe.parentNode.parentNode.hasAttribute('data-s9e-mediaembed')) && !isVisible(iframe));
	}

	var lazyIframes  = [],
		checkVisible = true,
		scrolling    = false;
	collectIframes();
	if (!lazyIframes.length)
	{
		return;
	}
	if (lazyIframes.length > 3)
	{
		setInterval(collectIframes, 60000);
	}

	addEventListener('scroll', setFlag);
	addEventListener('resize', setFlag);
	addEventListener('click', setFlag);

	setInterval(
		function ()
		{
			if (scrolling)
			{
				scrolling = false;
				checkVisible = true;
				return;
			}
			if (!checkVisible)
			{
				return;
			}
			checkVisible = false;
			var i = lazyIframes.length;
			while (--i >= 0)
			{
				var iframe = lazyIframes[i];
				if (isVisible(iframe))
				{
					iframe.contentWindow.location.replace(iframe.src);
					iframe.removeAttribute('data-lazy');
					lazyIframes.splice(i, 1);
				}
			}
		},
		100
	);
})();
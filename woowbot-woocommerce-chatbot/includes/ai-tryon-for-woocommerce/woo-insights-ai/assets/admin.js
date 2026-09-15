(function ($) {
	'use strict';

	$(document).ready(function () {
		const $form = $('#qcld-wia-form');
		if (!$form.length) return;

		const $input = $('#qcld-wia-question');
		const $chat = $('#qcld-wia-chat');
		const $button = $('#qcld-wia-submit').length ? $('#qcld-wia-submit') : $form.find('button[type="submit"]');
		const $clearButton = $('#qcld-wia-clear');

		const welcome = 'Hello! I am your AI Store Analyst. Ask me anything about your WooCommerce sales, trending products, customer behavior, or store performance.';
		const welcomeSuggestions = [
			'📈 Why did sales change in the last 30 days?',
			'🏆 What are my top 5 best-selling products?',
			'👥 Which customers are most likely to buy again?',
			'🏷️ Audit coupon usage and sales discounts.'
		];

		// Auto-resize textarea on input
		if ($input.length) {
			$input.on('input', function () {
				$(this).css('height', 'auto');
				$(this).css('height', Math.min(this.scrollHeight, 120) + 'px');
			});

			// Submit on Enter without Shift
			$input.on('keydown', function (e) {
				if (e.key === 'Enter' && !e.shiftKey) {
					e.preventDefault();
					$form.trigger('submit');
				}
			});
		}

		const formatValue = (value, currency) => {
			try {
				return currency ? new Intl.NumberFormat(undefined, { style: 'currency', currency }).format(value) : String(value);
			} catch (error) {
				return currency ? currency + ' ' + value : String(value);
			}
		};

		const getBotAvatarSvg = () => {
			return `<svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M12 8V4H8"></path><rect x="4" y="8" width="16" height="12" rx="2"></rect><path d="M2 14h2"></path><path d="M20 14h2"></path><path d="M9 13v2"></path><path d="M15 13v2"></path></svg>`;
		};

		const getUserAvatarSvg = () => {
			return `<svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M20 21v-2a4 4 0 0 0-4-4H8a4 4 0 0 0-4 4v2"></path><circle cx="12" cy="7" r="4"></circle></svg>`;
		};

		const renderChart = (config) => {
			const labels = Array.isArray(config.labels) ? config.labels : [];
			const values = Array.isArray(config.values) ? config.values.map(Number) : [];
			if (!labels.length || labels.length !== values.length) return null;

			const $wrap = $('<div>', { class: 'qcld-wia-chart' });
			if (config.title) {
				$('<h3>', { text: config.title }).appendTo($wrap);
			}

			const width = 720, height = 260, left = 68, right = 18, top = 16, bottom = 48;
			const plotW = width - left - right, plotH = height - top - bottom;
			const max = Math.max(...values, 1);
			const xFor = (index) => config.type === 'bar' ? left + (index + 0.5) * (plotW / values.length) : (values.length === 1 ? left + plotW / 2 : left + index * (plotW / (values.length - 1)));

			const svg = document.createElementNS('http://www.w3.org/2000/svg', 'svg');
			svg.setAttribute('viewBox', `0 0 ${width} ${height}`);
			svg.setAttribute('role', 'img');
			svg.setAttribute('aria-label', config.title || '');

			// Gradient definition
			const defs = document.createElementNS('http://www.w3.org/2000/svg', 'defs');
			defs.innerHTML = `
				<linearGradient id="wiaAreaGradient" x1="0" y1="0" x2="0" y2="1">
					<stop offset="0%" stop-color="#2563eb" stop-opacity="0.3"/>
					<stop offset="100%" stop-color="#2563eb" stop-opacity="0.0"/>
				</linearGradient>
			`;
			svg.appendChild(defs);

			const addSvg = (name, attrs, text) => {
				const el = document.createElementNS('http://www.w3.org/2000/svg', name);
				Object.entries(attrs).forEach(([key, val]) => el.setAttribute(key, val));
				if (text !== undefined) el.textContent = text;
				svg.appendChild(el);
				return el;
			};

			[0, 0.5, 1].forEach((ratio) => {
				const y = top + plotH * (1 - ratio);
				addSvg('line', { x1: left, y1: y, x2: width - right, y2: y, class: 'qcld-wia-grid' });
				addSvg('text', { x: left - 8, y: y + 4, class: 'qcld-wia-axis', 'text-anchor': 'end' }, formatValue(Math.round(max * ratio * 100) / 100, config.currency));
			});

			if (config.type === 'bar') {
				const slot = plotW / values.length;
				values.forEach((value, index) => {
					const h = Math.max(2, (value / max) * plotH);
					const x = left + index * slot + slot * 0.15;
					const bar = addSvg('rect', { x, y: top + plotH - h, width: slot * 0.7, height: h, rx: 4, class: 'qcld-wia-bar' });
					const tip = document.createElementNS('http://www.w3.org/2000/svg', 'title');
					tip.textContent = `${labels[index]}: ${formatValue(value, config.currency)}`;
					bar.appendChild(tip);
				});
			} else {
				const points = values.map((value, index) => `${xFor(index)},${top + plotH - (value / max) * plotH}`).join(' ');
				if (values.length > 1) {
					const areaPoints = `${xFor(0)},${top + plotH} ${points} ${xFor(values.length - 1)},${top + plotH}`;
					addSvg('polygon', { points: areaPoints, class: 'qcld-wia-area' });
				}
				addSvg('polyline', { points, class: 'qcld-wia-line' });
				values.forEach((value, index) => {
					const dot = addSvg('circle', { cx: xFor(index), cy: top + plotH - (value / max) * plotH, r: 4.5, class: 'qcld-wia-dot' });
					const tip = document.createElementNS('http://www.w3.org/2000/svg', 'title');
					tip.textContent = `${labels[index]}: ${formatValue(value, config.currency)}`;
					dot.appendChild(tip);
				});
			}

			const every = Math.max(1, Math.ceil(labels.length / 7));
			labels.forEach((label, index) => {
				if (index % every) return;
				addSvg('text', { x: xFor(index), y: height - 12, class: 'qcld-wia-axis', 'text-anchor': 'middle' }, String(label).slice(0, 14));
			});

			$wrap.append(svg);
			return $wrap;
		};

		const addMessage = (text, role, visualizations, suggestions, html, isTyping = false) => {
			const $row = $('<div>', { class: `qcld-wia-msg-row qcld-wia-row-${role}` });

			const $avatar = $('<div>', {
				class: `qcld-wia-avatar qcld-wia-avatar-${role === 'assistant' ? 'bot' : 'user'}`,
				html: role === 'assistant' ? getBotAvatarSvg() : getUserAvatarSvg()
			});
			$row.append($avatar);

			const $bubble = $('<div>', { class: 'qcld-wia-bubble' });

			if (isTyping) {
				const $typing = $('<div>', {
					class: 'qcld-wia-typing-indicator',
					html: '<span class="qcld-wia-typing-dot"></span><span class="qcld-wia-typing-dot"></span><span class="qcld-wia-typing-dot"></span> <span style="font-size:12px;color:#64748b;margin-left:6px;">Analyzing your store…</span>'
				});
				$bubble.append($typing);
			} else {
				const $copy = $('<div>', { class: 'qcld-wia-copy' });
				if (role === 'assistant' && html) {
					$copy.html(html);
				} else {
					$copy.text(text);
				}
				$bubble.append($copy);

				(visualizations || []).forEach((config) => {
					const $visual = renderChart(config);
					if ($visual) $bubble.append($visual);
				});

				if (role === 'assistant' && suggestions && suggestions.length) {
					const $choices = $('<div>', { class: 'qcld-wia-suggestions' });
					suggestions.slice(0, 4).forEach((question) => {
						const $choice = $('<button>', {
							type: 'button',
							class: 'qcld-wia-suggestion',
							text: question
						}).on('click', function () {
							$input.val(question.replace(/^[^\w\s]+/, '').trim());
							$form.trigger('submit');
						});
						$choices.append($choice);
					});
					$bubble.append($choices);
				}
			}

			$row.append($bubble);
			$chat.append($row);
			$chat.scrollTop($chat[0].scrollHeight);
			return $row;
		};

		const loadHistory = async () => {
			try {
				const data = await $.ajax({
					url: qcld_wia.historyEndpoint,
					method: 'GET',
					headers: { 'X-WP-Nonce': qcld_wia.nonce },
					dataType: 'json'
				});
				if (data && data.messages && data.messages.length) {
					$chat.empty();
					data.messages.forEach((message) => {
						addMessage(message.content, message.role, message.visualizations, message.suggestions, message.answer_html);
					});
				}
			} catch (error) {
				// Keep the welcome message if history cannot load
			}
		};

		const buildMetricItem = (label, value, change) => {
			const $item = $('<div>', { class: 'qcld-wia-metric-item' });
			const $info = $('<div>', { class: 'qcld-wia-metric-info' });

			$('<span>', { class: 'qcld-wia-metric-label', text: label }).appendTo($info);
			$('<span>', { class: 'qcld-wia-metric-val', text: value }).appendTo($info);
			$item.append($info);

			if (change !== null && change !== undefined) {
				const isUp = change >= 0;
				$('<span>', {
					class: `qcld-wia-metric-badge ${isUp ? 'qcld-wia-badge-up' : 'qcld-wia-badge-down'}`,
					html: `${isUp ? '▲ +' : '▼ -'}${Math.abs(change)}%`
				}).appendTo($item);
			}

			return $item;
		};

		const loadSnapshot = async () => {
			try {
				const data = await $.ajax({
					url: qcld_wia.snapshotEndpoint,
					method: 'GET',
					headers: { 'X-WP-Nonce': qcld_wia.nonce },
					dataType: 'json'
				});

				if (data.tip) {
					$('#qcld-wia-tip-text').text(data.tip);
				}

				const $metrics = $('#qcld-wia-metrics');
				$metrics.empty().append(
					buildMetricItem('Revenue', formatValue(data.summary.revenue, data.summary.currency), data.changes.revenue),
					buildMetricItem('Orders', String(data.summary.order_count), data.changes.orders),
					buildMetricItem('Avg Order Value', formatValue(data.summary.average_order_value, data.summary.currency), data.changes.aov)
				);

				const $trendTarget = $('#qcld-wia-side-trend');
				$trendTarget.empty();
				const $trend = renderChart({
					type: 'line',
					labels: data.trend.points.map((p) => p.period),
					values: data.trend.points.map((p) => p.revenue),
					currency: data.summary.currency
				});
				if ($trend) {
					$trendTarget.append($trend);
				} else {
					$('#qcld-wia-trend-card').hide();
				}

				const $productTarget = $('#qcld-wia-side-products');
				$productTarget.empty();
				const $products = renderChart({
					type: 'bar',
					labels: data.products.products.map((p) => p.product),
					values: data.products.products.map((p) => p.revenue),
					currency: data.summary.currency
				});
				if ($products) {
					$productTarget.append($products);
				} else {
					$('#qcld-wia-products-card').hide();
				}
			} catch (error) {
				$('#qcld-wia-tip-text').text('Ask the analyst for a recent sales review and store optimization opportunities.');
			}
		};

		// Submit form
		$form.on('submit', async function (e) {
			e.preventDefault();
			const question = $input.val().trim();
			if (!question || $button.prop('disabled')) return;

			addMessage(question, 'user');
			$input.val('').css('height', 'auto');
			$button.prop('disabled', true);

			const $waiting = addMessage('', 'assistant', [], [], '', true);

			try {
				const data = await $.ajax({
					url: qcld_wia.endpoint,
					method: 'POST',
					headers: {
						'Content-Type': 'application/json',
						'X-WP-Nonce': qcld_wia.nonce
					},
					data: JSON.stringify({ question }),
					dataType: 'json'
				});

				$waiting.remove();
				addMessage(
					data.answer || '',
					'assistant',
					data.visualizations || [],
					data.suggestions || [],
					data.answer_html || ''
				);
			} catch (xhr) {
				$waiting.remove();
				const errorMsg = (xhr.responseJSON && xhr.responseJSON.message) ? xhr.responseJSON.message : 'Could not connect to the AI service. Please check your network and API key settings.';
				addMessage(errorMsg, 'assistant');
			} finally {
				$button.prop('disabled', false);
				$input.trigger('focus');
			}
		});

		// Clear chat history with modern preload effect
		$clearButton.on('click', async function () {
			if ($clearButton.hasClass('is-loading')) return;

			const $btnText = $clearButton.find('span');
			const origText = $btnText.text();

			$clearButton.addClass('is-loading').prop('disabled', true);
			$btnText.text('Resetting…');
			$chat.addClass('is-resetting');

			try {
				await $.ajax({
					url: qcld_wia.historyEndpoint,
					method: 'DELETE',
					headers: { 'X-WP-Nonce': qcld_wia.nonce }
				});

				$chat.empty();
				addMessage(welcome, 'assistant', [], welcomeSuggestions);

				$clearButton.removeClass('is-loading').addClass('is-success');
				$btnText.text('Reset Complete!');

				setTimeout(() => {
					$clearButton.removeClass('is-success');
					$btnText.text(origText);
				}, 1500);
			} catch (error) {
				$clearButton.removeClass('is-loading');
				$btnText.text(origText);
			} finally {
				$chat.removeClass('is-resetting');
				$clearButton.prop('disabled', false);
				$input.trigger('focus');
			}
		});

		// Initial load
		addMessage(welcome, 'assistant', [], welcomeSuggestions);
		loadHistory();
		loadSnapshot();
	});
})(jQuery);

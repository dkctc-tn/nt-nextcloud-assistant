/**
 * SPDX-FileCopyrightText: 2026 DK Consultants & Technologies Corp and MoreDKon contributors
 * SPDX-FileCopyrightText: 2023 Nextcloud GmbH and Nextcloud contributors
 * SPDX-License-Identifier: AGPL-3.0-or-later
 */

import { createApp } from 'vue'
import AdminSystemSettings from './components/AdminSystemSettings.vue'

const app = createApp(AdminSystemSettings)
app.mixin({ methods: { t, n } })
app.mount('#nt-assistant-system-settings')

export default function deleteSession() {
  return `${ajaxurl}?action=delete_integration_api_session&_wpnonce=${wpApiSettingsIntegrationAPI.nonce_configs}`;
}
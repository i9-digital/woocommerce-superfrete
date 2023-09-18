export default function deleteSession() {
  return `${ajaxurl}?action=delete_superfrete_session&_wpnonce=${wpApiSettingsSuperfrete.nonce_configs}`;
}
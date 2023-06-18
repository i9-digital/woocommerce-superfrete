'use strict'
import Axios from 'axios'

const balance = {
    namespaced: true,
    state: {
        limit: null,
        limitEnabled: null,
        balance: null,
        username: null,
        email: null
    },
    mutations: {
        setLimits: (state, data) => {
            state.limit = data.shipments;
            state.limitEnabled = data.shipments_available;
        },
        setBalance: (state, data) => {
            state.balance = data;
        },
        setUser: (state, data) => {

        }
    },  
    getters: {
        getLimit: state => state.limit,
        getLimitEnabled: state => state.limitEnabled,
        getBalance: state => state.balance,
        getUsername: state => state.username,
        getEmail: state => state.email
    },
    actions: {
        setLimits: ({commit}, data) => {        
            Axios.get(`${ajaxurl}?action=sf_me&_wpnonce=${wpApiSettingsIntegrationAPI.nonce_users}`, data).then(response => {
                commit('setLimits', response.data.limits)
            })
            
        },
        setBalance: ({commit}, data) => {        
            Axios.get(`${ajaxurl}?action=get_sf_balance&_wpnonce=${wpApiSettingsIntegrationAPI.nonce_users}`, data).then(response => {
                commit('setBalance', response.data.balance)
            })
            
        },
        setUser: ({commit}, data) => {        
            Axios.get(`${ajaxurl}?action=user_sf_info`).then(response => {
                commit('setUser', response.data.user)
            })
            
        }
    }
}

export default balance
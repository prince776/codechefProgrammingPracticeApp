import React, { Component } from 'react'
import { Redirect } from 'react-router-dom';

import axiosInstance from '../utils/axiosInstance';

class Logout extends Component {

    constructor(props) {
        super(props);
    }

    onLogout = () => {
        let params = {
            token: localStorage.getItem('token')
        }
        axiosInstance.get('/api/user/logout', { params })
            .then(res => {
                localStorage.removeItem('token');
                this.props.setUser('', '', false);
            })
            .catch(err => alert(err.response.data.data));
    }

    render() {
        return (
            <div>
                <div role="button" onClick={this.onLogout} className="nav-link" to='/login'>Log Out</div>
            </div>
        )
    }
}

export default Logout;

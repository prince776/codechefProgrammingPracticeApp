import React, { Component } from 'react'
import { Link, Redirect } from 'react-router-dom';

import axiosInstance from '../utils/axiosInstance';

class Login extends Component {

    constructor(props) {
        super(props);
        this.state = {
            username: '',
            password: '',
            message: ''
        }
    }

    onInputBoxChange = (e) => {
        this.setState({
            [e.target.name]: e.target.value
        })
    }

    preCheck = () => {
        let can = true;
        if (!this.state.username) {
            this.setState({ message: 'Please enter username' });
            can = false;
            return can;
        }
        if (!this.state.password) {
            this.setState({ message: 'Please enter password' });
            can = false;
        }
        return can;
    }

    onLogin = () => {
        if (!this.preCheck()) return;

        let req_url = '/api/user/login';
        let params = {
            username: this.state.username,
            password: this.state.password
        }
        axiosInstance.post(req_url, params)
            .then(res => {
                console.log(res.data.data);
                this.setState({
                    message: res.data.msg,
                }, () => this.props.setUser(res.data.data.username, res.data.data.token, true))
            }).catch(err => {
                this.setState({
                    message: err.response.data.data
                })
            })
    }

    render() {
        if (this.props.isAuthenticated)
            return (<Redirect to='/' />);

        return (
            <div className='container-fluid p-5'>
                <div className='text-center text-black '>

                    <div >
                        <h1>Log In</h1>
                    </div>
                    <hr />
                    < form className='p-4 m-3' >
                        <div className="form-group row">
                            <label className="col-sm-2 col-form-label">Username</label>
                            <div className="col-sm-10">
                                <input type="text" name='username' onChange={this.onInputBoxChange} className="form-control" placeholder="Username" />
                            </div>
                        </div>
                        <div className="form-group row">
                            <label className="col-sm-2 col-form-label">Password</label>
                            <div className="col-sm-10">
                                <input type="password" name='password' onChange={this.onInputBoxChange} className="form-control" placeholder="Password" />
                            </div>
                        </div>

                        <button type="button" onClick={this.onLogin} className="btn btn-info">Log In</button>
                        <div className='row p-2'>
                            <div className='col'>
                                <Link to='/signup' className='btn btn-secondary'>Do not have an Account? Sign up here</Link>
                            </div>
                        </div>
                        <h6 className='text-center text-info'>{this.state.message}</h6>
                    </form>
                </div>
            </div>
        )
    }
}

export default Login;
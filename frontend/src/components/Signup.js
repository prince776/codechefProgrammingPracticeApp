import React, { Component } from 'react'
import { Link, Redirect } from 'react-router-dom';

import axiosInstance from '../utils/axiosInstance';

class Signup extends Component {

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

    onSignUp = () => {
        if (!this.preCheck()) return;

        let req_url = '/api/user/signup';
        let params = {
            username: this.state.username,
            password: this.state.password
        }
        axiosInstance.get(req_url, { params })
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
            <div className='container-fluid text-center text-black p-5'>

                <div >
                    <h1>Sign Up</h1>
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

                    <button type="button" onClick={this.onSignUp} className="btn btn-info">Sign Up</button>
                    <div className='row p-2'>
                        <div className='col'>
                            <Link to='/login' className='btn btn-secondary'>Already have an Account? Sign In here</Link>
                        </div>
                    </div>
                    <h6 className='text-center text-info'>{this.state.message}</h6>
                </form >

            </div >
        )
    }
}

export default Signup;
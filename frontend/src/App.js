import React, { Component } from 'react'

import Header from './layout/Header';
import Routes from './Routes';

import axiosInstance from './utils/axiosInstance';

class App extends Component {

  constructor(props) {
    super(props);

    this.state = {
      username: '',
      isAuthenticated: false
    }
  }

  setUser = (username, token, isAuthenticated) => {
    this.setState({
      username: username,
      isAuthenticated: isAuthenticated
    });
    localStorage.setItem('token', token);
  }

  authenticateUser = () => {
    axiosInstance.post('/api/user/authenticate', { token: localStorage.getItem('token') })
      .then(res => this.setState({ username: res.data.data.username, isAuthenticated: true }))
      .catch(err => this.setState({ username: '', isAuthenticated: false }));
  }

  componentDidMount() {
    this.authenticateUser();
  }

  render() {
    return (
      <div>
        <Header username={this.state.username} isAuthenticated={this.state.isAuthenticated} setUser={this.setUser} />
        <Routes username={this.state.username} isAuthenticated={this.state.isAuthenticated} setUser={this.setUser} />
      </div>
    );
  }
}

export default App;

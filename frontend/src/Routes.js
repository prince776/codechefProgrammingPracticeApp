import React, { Component } from 'react';
import { Switch, Route, withRouter } from 'react-router-dom';

import Home from './components/Home';
import Signup from './components/Signup';
import Login from './components/Login';

class Routes extends Component {
    constructor(props) {
        super(props);
    }

    render() {
        return (
            <Switch>
                <Route exact path="/signup" render={() => <Signup setUser={this.props.setUser} username={this.props.username} isAuthenticated={this.props.isAuthenticated} />} />
                <Route exact path="/login" render={() => <Login setUser={this.props.setUser} username={this.props.username} isAuthenticated={this.props.isAuthenticated} />} />
                <Route path="/" render={() => <Home setUser={this.props.setUser} username={this.props.username} isAuthenticated={this.props.isAuthenticated} />} />
            </Switch>
        );
    }
}

export default withRouter(Routes);
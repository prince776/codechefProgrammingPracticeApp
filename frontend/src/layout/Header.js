import React, { Component, Fragment } from 'react'
import { Link } from 'react-router-dom';

import Logout from '../components/Logout';

class Header extends Component {

    render() {
        return (
            <nav className="navbar navbar-expand-lg navbar-dark bg-dark">
                <div className="navbar-brand" href="#">Programming Practice App</div>
                <button className="navbar-toggler" type="button" data-toggle="collapse" data-target="#navbarSupportedContent" aria-controls="navbarSupportedContent" aria-expanded="false" aria-label="Toggle navigation">
                    <span className="navbar-toggler-icon"></span>
                </button>

                <div className="collapse justify-content-end  navbar-collapse" id="navbarSupportedContent">
                    <ul className="navbar-nav">
                        <li className="nav-item active">
                            <Link className="nav-link" to="/">Home<span className="sr-only">(current)</span></Link>
                        </li>
                        {/* <li className="nav-item">
                            <Link className="nav-link" to='/browseTags'>Browse Tags</Link>
                        </li> */}

                        {this.props.isAuthenticated == false ?
                            <Fragment>
                                <li className="nav-item">
                                    <Link className="nav-link" to="/signup">Sign up</Link>
                                </li>
                                <li className="nav-item">
                                    <Link className="nav-link" to='/login'>Log in</Link>
                                </li>
                            </Fragment>
                            :
                            <Fragment>
                                <li className="nav-item">
                                    <Logout setUser={this.props.setUser} />
                                </li>
                                <li className="nav-item">
                                    <span className="nav-link active text-uppercase">{this.props.username}</span>
                                </li>
                            </Fragment>
                        }

                    </ul>
                </div>
            </nav>
        )
    }
}

export default Header;
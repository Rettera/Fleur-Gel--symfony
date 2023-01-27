node {
  stage('SCM') {
    checkout scm
  }
  stage('SonarQube Analysis') {
    def scannerHome = tool 'SonarQube';
    withSonarQubeEnv() {
      sh "${scannerHome}/bin/sonar-scanner"
    }
  }
}
node('DOCKER'){
    stage('test'){
        userInput = input(id: 'userInput',
        message: 'Do you want to release this build?',
        parameters: [[$class: 'PasswordParameterDefinition',
            defaultValue: "",
            name: 'password',
            description: 'Password']])

        sh "echo User Input is" + userInput
    }
}

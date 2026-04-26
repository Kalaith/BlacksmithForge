import React from 'react';

interface FramedButtonProps extends React.ButtonHTMLAttributes<HTMLButtonElement> {
  icon?: React.ReactNode;
  variant?: 'primary' | 'secondary' | 'ghost';
}

const FramedButton: React.FC<FramedButtonProps> = ({
  icon,
  variant = 'secondary',
  className = '',
  children,
  ...props
}) => (
  <button className={`framed-button framed-button--${variant} ${className}`.trim()} {...props}>
    {icon ? <span className="framed-button__icon">{icon}</span> : null}
    <span>{children}</span>
  </button>
);

export default FramedButton;


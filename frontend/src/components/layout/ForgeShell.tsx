import React from 'react';
import { uiAssets } from '../../data/ui-assets';

const ForgeShell: React.FC<{ children: React.ReactNode }> = ({ children }) => (
  <div
    className="forge-shell"
    style={{ '--forge-bg-image': `url("${uiAssets.background}")` } as React.CSSProperties}
  >
    <div className="forge-shell__shade" />
    <div className="forge-shell__embers" aria-hidden="true">
      <span />
      <span />
      <span />
      <span />
      <span />
      <span />
    </div>
    <div className="forge-shell__content">{children}</div>
  </div>
);

export default ForgeShell;

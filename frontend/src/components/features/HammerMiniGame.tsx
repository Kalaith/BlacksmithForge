import React, { useRef, useState, useEffect } from 'react';

interface HammerMiniGameProps {
  maxClicks: number;
  onHammerHit: () => Promise<void>;
  craftingStarted: boolean;
  currentClicks?: number;
  currentAccuracy?: number;
}

const targetWidth = 60; // px
const cursorWidth = 20; // px
const barWidth = 300; // px
const barHeight = 24; // px

const HammerMiniGame: React.FC<HammerMiniGameProps> = ({
  maxClicks,
  onHammerHit,
  craftingStarted,
  currentClicks = 0,
  currentAccuracy = 0,
}) => {
  const [cursorPos, setCursorPos] = useState(0);
  const [targetPos, setTargetPos] = useState(Math.random() * (barWidth - targetWidth));
  const [movingRight, setMovingRight] = useState(true);
  const [isHitting, setIsHitting] = useState(false);
  const intervalRef = useRef<ReturnType<typeof setInterval> | null>(null);

  // Animate cursor
  useEffect(() => {
    if (!craftingStarted || currentClicks >= maxClicks || isHitting) return;

    intervalRef.current = setInterval(() => {
      setCursorPos(pos => {
        let next = movingRight ? pos + 8 : pos - 8;
        if (next <= 0) {
          setMovingRight(true);
          next = 0;
        } else if (next >= barWidth - cursorWidth) {
          setMovingRight(false);
          next = barWidth - cursorWidth;
        }
        return next;
      });
    }, 24);

    return () => {
      if (intervalRef.current) {
        clearInterval(intervalRef.current);
      }
    };
  }, [craftingStarted, currentClicks, movingRight, maxClicks, isHitting]);

  // Reset on start
  useEffect(() => {
    if (craftingStarted && currentClicks === 0) {
      setCursorPos(0);
      setTargetPos(Math.random() * (barWidth - targetWidth));
      setMovingRight(true);
      setIsHitting(false);
    }
  }, [craftingStarted, currentClicks]);

  // Generate new target after each hit
  useEffect(() => {
    if (currentClicks > 0 && currentClicks < maxClicks) {
      setTargetPos(Math.random() * (barWidth - targetWidth));
      setIsHitting(false);
    }
  }, [currentClicks, maxClicks]);

  const handleHammerClick = async () => {
    if (currentClicks >= maxClicks || isHitting) return;

    setIsHitting(true);

    try {
      // Call the backend to process the hammer hit
      await onHammerHit();
    } catch (error) {
      console.error('Hammer hit failed:', error);
      setIsHitting(false);
    }
  };

  const isGameComplete = currentClicks >= maxClicks;

  return (
    <div className="hammer-mini-game">
      <h4>Hammer Mini-game</h4>
      <div style={{ marginBottom: 8 }}>
        Hits: {currentClicks} / {maxClicks}
      </div>
      <div style={{ marginBottom: 16 }}>Accuracy: {currentAccuracy}%</div>

      {!isGameComplete && (
        <>
          <div
            style={{
              position: 'relative',
              width: barWidth,
              height: barHeight,
              background: '#eee',
              borderRadius: 8,
              margin: '16px 0',
              border: '2px solid #ccc',
            }}
          >
            {/* Target zone */}
            <div
              style={{
                position: 'absolute',
                left: targetPos,
                top: 0,
                width: targetWidth,
                height: barHeight,
                background: 'var(--color-success)',
                opacity: 0.7,
                borderRadius: 6,
              }}
            />

            {/* Moving cursor */}
            <div
              style={{
                position: 'absolute',
                left: cursorPos,
                top: 0,
                width: cursorWidth,
                height: barHeight,
                background: isHitting ? 'var(--color-warning)' : 'var(--color-primary)',
                borderRadius: 6,
                boxShadow: '0 0 4px #333',
                transition: isHitting ? 'background-color 0.2s' : 'none',
              }}
            />
          </div>

          <button
            className="btn btn--primary"
            onClick={handleHammerClick}
            disabled={isHitting || isGameComplete}
          >
            {isHitting ? '⚒️ HITTING...' : '⚒️ HAMMER!'}
          </button>
        </>
      )}

      {isGameComplete && (
        <div className="game-complete">
          <div className="status status--info">
            Hammering complete! Final accuracy: {currentAccuracy}%
          </div>
        </div>
      )}
    </div>
  );
};

export default HammerMiniGame;

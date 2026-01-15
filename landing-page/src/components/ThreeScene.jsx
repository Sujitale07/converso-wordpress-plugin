import { useRef, useMemo } from 'react'
import { Canvas, useFrame } from '@react-three/fiber'
import { Float, Sphere, MeshDistortMaterial, PerspectiveCamera } from '@react-three/drei'
import * as THREE from 'three'

function FloatingShapes() {
  const group = useRef()
  
  useFrame((state) => {
    const t = state.clock.getElapsedTime()
    group.current.rotation.x = Math.cos(t / 4) / 8
    group.current.rotation.y = Math.sin(t / 4) / 8
    group.current.position.y = (1 + Math.sin(t / 1.5)) / 10
  })

  return (
    <group ref={group}>
      {/* Abstract Spheres */}
      <Float speed={2} rotationIntensity={0.5} floatIntensity={1}>
        <Sphere args={[1, 64, 64]} position={[-3, 2, -5]}>
          <MeshDistortMaterial
            color="#4f46e5"
            speed={3}
            distort={0.4}
            radius={1}
            opacity={0.1}
            transparent
          />
        </Sphere>
      </Float>

      <Float speed={5} rotationIntensity={2} floatIntensity={2}>
        <Sphere args={[0.5, 64, 64]} position={[4, -2, -4]}>
          <MeshDistortMaterial
            color="#8b5cf6"
            speed={5}
            distort={0.5}
            radius={1}
            opacity={0.15}
            transparent
          />
        </Sphere>
      </Float>

      <Float speed={3} rotationIntensity={1} floatIntensity={1}>
        <Sphere args={[0.2, 32, 32]} position={[2, 3, -3]}>
          <meshStandardMaterial color="#10b981" emissive="#10b981" emissiveIntensity={2} />
        </Sphere>
      </Float>
    </group>
  )
}

function Grid() {
    return (
        <gridHelper 
            args={[100, 50, '#1e293b', '#0f172a']} 
            position={[0, -5, 0]} 
            rotation={[Math.PI / 2, 0, 0]} 
        />
    )
}

export default function ThreeScene() {
  return (
    <div className="fixed inset-0 -z-10 bg-[#030712]">
      <Canvas>
        <PerspectiveCamera makeDefault position={[0, 0, 10]} fov={50} />
        <ambientLight intensity={0.5} />
        <pointLight position={[10, 10, 10]} intensity={1.5} color="#8b5cf6" />
        <pointLight position={[-10, -10, -10]} intensity={1} color="#10b981" />
        
        <FloatingShapes />
        <Grid />
        
        <fog attach="fog" args={['#030712', 5, 20]} />
      </Canvas>
    </div>
  )
}
